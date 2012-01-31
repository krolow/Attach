<?php
App::uses('Attachment', 'Attach.Model');

class UploadBehavior extends ModelBehavior {


	public function setup(Model $model, $config = array()) {
		$this->config[$model->alias] = $config;
		$this->types[$model->alias] = array_keys($this->config[$model->alias]);

		foreach ($this->types[$model->alias] as $index => $type) {
			$folder = $this->getUploadFolder($model, $type);
			$this->isWritable($this->getUploadFolder($model, $type));
			$this->setRelation($model, $this->types[$model->alias][$index]);
		}
	}

	public function setRelation(Model $model, $type) {
		$type = Inflector::camelize($type);
		$relation = 'hasOne';

		//case is defined multiple is a hasMany
		if (isset($this->config[$model->alias][$type]['multiple'])
			&& $this->config[$model->alias][$type]['multiple'] == true) {
			$relation = 'hasMany';
		}

		$model->{$relation}['Attachment' . $type] = array(
			'className' => 'Attachment',
			'foreignKey' => 'foreign_key',
			'dependent' => true,
			'conditions' => array(
				'Attachment' . $type . '.model' => $model->alias,
				'Attachment' . $type . '.type' => strtolower($type)),
			'fields' => '',
			'order' => ''
		);
	}

	/**
	 * Check if the file extension it's correct
	 *
	 * @param array $check Array of data from the file that is been checking
	 * @return bool Return true in case of valid and false in case of invalid
	 * @access public
	 */
	public function extension($model, $check, $extensions) {
		$check = array_shift($check);
		if (isset($check['name'])) {
			return in_array($this->getFileExtension($check['name']), $extensions);
		}

		return false;
	}

	/**
	 * Check if the mime type it's correct
	 *
	 * @param array $check Array of data from the file that is been checking
	 * @return bool Return true in case of valid and false in case of invalid
	 * @access public
	 */
	public function mime($model, $check, $mimes) {
		$check = array_shift($check);

		if (isset($check['tmp_name']) && is_file($check['tmp_name'])) {
			$info = $this->getFileMime($model, $check['tmp_name']);

			return in_array($info, $mimes);
		}

		return false;
	}

	public function size($model, $check, $size) {
		$check = array_shift($check);

		return $size >= $check['size'];
	}

    /**
     * Check if the image fits within given dimensions
     *
     * @param array $check Array of data from the file that is been checked
     * @param int $width Maximum width in pixels
     * @param int $height Maximum height in pixels
     * @return bool Return true if image fits withing given dimensions
     * @access public
     */
	public function maxDimensions($model, $check, $width, $height) {
		$check = array_shift($check);

		if (isset($check['tmp_name']) && is_file($check['tmp_name'])) {
			$info = getimagesize($check['tmp_name']);

			return ($info && $info[0] <= $width && $info[1] <= $height);
		}

		return false;
	}

	public function getFileMime($model, $file) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$info = finfo_file($finfo, $file);

		return $info;
	}

	/**
	 * Check if the mime type it's correct
	 *
	 * @param array $value Array of data from the file that is been checking
	 * @return bool Return true in case of valid and false in case of invalid
	 * @access protected
	 */
	public function getFileExtension($filename) {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

	/**
	 * Return the upload folder that was set
	 *
	 * @return string Path for the upload folder
	 * @access public
	 */
	public function getUploadFolder($model, $type) {
		return APP . str_replace('{DS}', DS, $this->config[$model->alias][$type]['dir']) . DS;
	}

	public function isWritable($dir) {
		if (is_dir($dir) && is_writable($dir)) {
			return true;
		}

		throw new Exception('Folder is not writable: ' .  $dir);
	}


	public function afterSave(Model $model, $created) {
		foreach ($this->types[$model->alias] as $type) {
			//set multiple as false by standard
			$multiple = false;

			if (isset($this->config[$model->alias][$type]['multiple'])
				&& $this->config[$model->alias][$type]['multiple'] === true) {
				$multiple = true;
				$check = is_array($model->data[$model->alias][$type]);
			} else {
				$check = isset($model->data[$model->alias][$type]['tmp_name'])
					&& !empty($model->data[$model->alias][$type]['tmp_name']);
			}

			//case has the file update :)
			if ($check) {
				if ($multiple) {
					foreach ($model->data[$model->alias][$type] as $index => $value) {
						$this->saveFile($model, $type, $index);
					}
				} else {
					$this->saveFile($model, $type);
				}
			}
		}
	}

	public function beforeDelete($model, $cascade = true) {
		if ($cascade = true) {
			foreach ($this->types[$model->alias] as $type) {
				$className = 'Attachment'. Inflector::camelize($type);

				$attachments = $model->{$className}->find('all', array(
					'conditions' => array(
						'model' => $model->name,
						'foreign_key' => $model->id,
					),
				));

				foreach ($attachments as $attach) {
					$this->deleteAllFiles($model, $attach);
				}
			}
		}

		return $cascade;
	}

	public function saveFile(Model $model, $type, $index = null) {
		$uploadData = $model->data[$model->alias][$type];

		if (!is_null($index)) {
			$uploadData = $uploadData[$index];
		}

		$file = $this->generateName($model, $type, $index);
		$attach = $this->_saveAttachment($model, $type, $file);

		//move file
		copy($uploadData['tmp_name'], $file);
		@unlink($uploadData['tmp_name']);

		if (isset($this->config[$model->alias][$type]['thumbs'])) {
			$info = getimagesize($file);
			if (!$info) {
				throw new CakeException(sprintf('The file %s is not an image', $file));
			}
			$this->__createThumbs($model, $file, $type);
		}
	}

	public function deleteAllFiles($model, $attachment) {
		$attachment = array_shift($attachment);

		$dir = $this->getUploadFolder($model, $attachment['type']);

		//delete the original file
		$this->deleteFile($dir . $attachment['filename']);

		//check if exists thumbs to be deleted too
		$files = glob($dir . '*.' . $attachment['filename']);
		if (is_array($files)) {
			foreach ($files as $fileToDelete) {
				$this->deleteFile($fileToDelete);
			}
		}
	}

	public function deleteFile($filename) {
		if (file_exists($filename)) {
			return unlink($filename);
		}

		return false;
	}

	protected function _saveAttachment(Model $model, $type, $filename, $edit = null) {
		$className = 'Attachment'. Inflector::camelize($type);

		$attachment = $model->{$className}->find('first', array(
			'conditions' => array(
				'foreign_key' => $model->id,
				'model' => $model->alias,
				'type' => $type,
				'filename' => $filename,
			),
		));

		$data = array(
			$className => array(
				'model' => $model->alias,
				'foreign_key' => $model->id,
				'filename' => basename($filename),
				'type' => $type,
			),
		);

		if ($attachment) {
			$this->deleteAllFiles($model, $attachment);
			$data[$className]['id'] = $attachment[$className]['id'];
		} else {
			$model->{$className}->create();
		}

		$model->data += $model->{$className}->save($data);
	}

	public function generateName(Model $model, $type, $index = null) {
		$dir = $this->getUploadFolder($model, $type);

		if (is_null($index)) {
			$extension = $this->getFileExtension($model->data[$model->alias][$type]['name']);
		} else {
			$extension = $this->getFileExtension($model->data[$model->alias][$type][$index]['name']);
		}

		if (!is_null($index)) {
			return $dir . $type . '_' . $index . '_' . $model->id . '.' . $extension;
		}

		return $dir . $type . '_' . $model->id . '.' . $extension;
	}

	public function __createThumbs($model, $file, $type) {
		$imagine = $this->getImagine();
		$image = $imagine->open($file);

		$thumbName = basename($file);
		foreach ($this->config[$model->alias][$type]['thumbs'] as $key => $values) {
			$this->__generateThumb(array(
				'name' => str_replace($thumbName, $key . '.' . $thumbName, $file),
				'w' => $values['w'],
				'h' => $values['h'],
			), $image, $values['crop']);
		}
	}

	private function getImagine() {
		if (!interface_exists('Imagine\Image\ImageInterface')) {
			if (is_file(VENDORS . 'imagine.phar')) {
				require_once 'phar://' . VENDORS . 'imagine.phar';
			} else {
				throw new CakeException(sprintf('You should add in your vendors folder %s, the imagine.phar,
				you can download here: https://github.com/avalanche123/Imagine', VENDORS));
			}
		}

		return new \Imagine\Gd\Imagine();
	}

	public function createThumb($filename, $name, $width, $height, $crop = false) {
		$imagine = $this->getImagine();
		$image = $imagine->open($filename);

		$this->__generateThumb(array(
			'w' => $width,
			'h' => $height,
			'name' => $name,
		), $image, $crop);
	}

	private function __generateThumb($thumb, $image, $crop = false) {
		if ($crop) {
			$mode =  Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
		} else {
			$mode = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
		}

		$thumbnail = $image->thumbnail(new Imagine\Image\Box($thumb['w'], $thumb['h']), $mode);
		$thumbnail->save($thumb['name']);
	}

}
?>
