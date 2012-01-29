<?php
App::uses('Attachment', 'Attach.Model');

class UploadBehavior extends ModelBehavior {


	public function setup(Model $model, $config = array()) {
		$this->config = $config;
		$this->types = array_keys($this->config);

		foreach ($this->types as $index => $type) {
			$folder = $this->getUploadFolder($model, $type);
			$this->isWritable($this->getUploadFolder($model, $type));
			$this->setRelation($model, $this->types[$index]);
		}
	}

	public function setRelation(Model $model, $type) {

		$type = Inflector::camelize($type);
		$relation = 'hasOne';

		//case is defined multiple is a hasMany
		if (isset($this->config[$type]['multiple']) && $this->config[$type]['multiple'] == true) {
			$relation = 'hasMany';
		}

		$model->{$relation}['Attachment' . $type] = array(
			'className' => 'Attachment',
			'foreignKey' => 'foreign_key',
			'dependent' => true,
			'conditions' => array(
				'Attachment' . $type . '.model' => $model->name,
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
		return APP . str_replace('{DS}', DS, $this->config[$type]['dir']) . DS;
	}

	public function isWritable($dir) {
		if (is_dir($dir) && is_writable($dir)) {
			return true;
		}

		throw new Exception('Folder is not writable: ' .  $dir);
	}


	public function afterSave(Model $model, $created) {
		foreach ($this->types as $type) {
			//case has the file update :)
			if (isset($model->data[$model->alias][$type]['tmp_name']) &&
				!empty($model->data[$model->alias][$type]['tmp_name'])) {
				$this->saveFile($model, $type);
			}
		}
	}

	public function beforeDelete($model, $cascade = true) {
		if ($cascade = true) {
			foreach ($this->types as $type) {
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

	public function saveFile(Model $model, $type) {
		if (isset($model->data[$model->alias][$type]['tmp_name'])
			&& !empty($model->data[$model->alias][$type]['tmp_name'])) {
			$file = $this->generateName($model, $type);
			$attach = $this->_saveAttachment($model, $type, $file);

			//move file
			copy($model->data[$model->alias][$type]['tmp_name'], $file);
			@unlink($this->data[$model->alias][$type]['tmp_name']);

			if (isset($this->config[$type]['thumbs'])) {
				$info = getimagesize($file);
				if (!$info) {
					throw new CakeException(sprintf('The file %s is not an image', $file));
				}
				$this->__createThumbs($file, $type);
			}
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
				'model' => $model->name,
				'type' => $type,
			),
		));

		$data = array(
			$className => array(
				'model' => $model->name,
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

	public function generateName(Model $model, $type) {
		$dir = $this->getUploadFolder($model, $type);
		$extension = $this->getFileExtension($model->data[$model->alias][$type]['name']);

		return $dir . $type . '_' . $model->id . '.' . $extension;
	}

	public function __createThumbs($file, $type) {
		$imagine = $this->getImagine();
		$image = $imagine->open($file);

		$thumbName = basename($file);
		foreach ($this->config[$type]['thumbs'] as $key => $values) {
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
