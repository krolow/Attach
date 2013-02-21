<?php
/**
* Upload for CakePHP.
*
* PHP 5.3
*
*
* Licensed under The MIT License
* Redistributions of files must retain the above copyright notice.
*
* @version       1.0
* @link          https://github.com/krolow/Attach
* @package       Attach.Model.Behavior
* @author        Vinícius Krolow <krolow@gmail.com>
* @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
*/

App::uses('Attachment', 'Attach.Model');

class UploadBehavior extends ModelBehavior
{
    
    /**
    * Imagine Github URL
    * 
    * @var string
    */
    const IMAGINE_URL = 'https://github.com/avalanche123/Imagine';

    /**
     * Set what are the multiple models
     *
     * @var array
     */
    private $multiple = array();

    /**
     * Setup this behavior with the specified configuration settings.
     *
     * @param Model $model  Model using this behavior
     * @param array $config Configuration settings for $model
     *
     * @return void
     * @access public
     */
    public function setup(Model $model, $config = array())
    {
        $this->config[$model->alias] = $config;
        $this->types[$model->alias]  = array_keys($this->config[$model->alias]);

        foreach ($this->types[$model->alias] as $index => $type) {
            $folder = $this->getUploadFolder($model, $type);
            $this->isWritable($this->getUploadFolder($model, $type));
            $this->setRelationModel(
                $model,
                $this->types[$model->alias][$index]
            );
        }
    }

    /**
     * Create the relation bettween the model and the attachment model for each
     * type of file setted in the config
     *
     * @param Model  $model Model using this behavior
     * @param string $type  Type of the file upload
     *
     * @access protected
     * @return void
     */
    protected function setRelationModel(Model $model, $type)
    {
        $type     = Inflector::camelize($type);
        $relation = 'hasOne';

        //case is defined multiple is a hasMany
        if ($this->_isMultiple($model, $type)) {
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
     * Check if the given file type is multiple or not
     *
     * @param Model  $model Model using this behavior
     * @param string $type  Type of the file upload
     *
     * @access private
     * @return bool
     */
    private function _isMultiple(Model $model, $type)
    {
        return isset($this->config[$model->alias][$type]['multiple'])
            && $this->config[$model->alias][$type]['multiple'] == true;
    }

    /**
     * Check if it's necessary validate the file
     *
     * @param Model  $model      Model using this behavior
     * @param string $validation Name of the validation
     * @param array  $check      Data array of file
     *
     * @access public
     * @return boolean
     */
    public function shouldValidate($model, $validation, $check)
    {
        if ($this->isPostFileDataEmpty($model, $check)) {
            return !$this->isRequired($model, $validation, $check);
        }

        return false;
    }

    /**
     * Check if the given data is empty
     *
     * @param Model $model Model using this behavior
     * @param array $file  File data
     *
     * @access public
     * @return boolean
     */
    public function isPostFileDataEmpty($model, $file)
    {
        if (!is_array($file)) {
            return false;
        }
        $file = array_shift($file);

        return empty($file['name']) && $file['size'] === 0;
    }

    /**
     * Check if the file is required
     *
     * @param Model  $model      Model using this behavior
     * @param stirng $validation Method name
     * @param array  $check      Data arary of file
     *
     * @access public
     * @return boolean
     */
    public function isRequired($model, $validation, $check)
    {
        $key = key($check);

        if (!isset($model->validate[$key])
            || !isset($model->validate[$key]['required'])
        ) {
            return false;
        }

        return (bool)$model->validate[$key]['required'];
    }

    /**
     * Check if the file extension it's correct
     *
     * @param Model $model      Model using this behavior
     * @param array $check      File to be checked
     * @param array $extensions The list of allowed extensions
     *
     * @access public
     * @return bool Return true in case of valid and false in case of invalid
     */
    public function extension(Model $model, $check, $extensions)
    {

        if ($this->shouldValidate($model, __METHOD__, $check)) {
            return true;
        }

        $check = array_shift($check);

        if (isset($check['name'])) {
            return in_array(
                $this->getFileExtension(
                    $model,
                    $check['name']
                ),
                $extensions
            );
        }

        return false;
    }

    /**
     * Check if the mime type it's correct
     *
     * @param Model $model Model using this behavior
     * @param array $check File to be checked
     * @param array $mimes The list of allowed mime types
     *
     * @access public
     * @return bool Return true in case of valid and false in case of invalid
     */
    public function mime(Model $model, $check, $mimes)
    {
        if ($this->shouldValidate($model, __METHOD__, $check)) {
            return true;
        }

        $check = array_shift($check);

        if (isset($check['tmp_name']) && file_exists($check['tmp_name'])) {
            $info = $this->getFileMime($model, $check['tmp_name']);

            return in_array($info, $mimes);
        }

        return false;
    }

    /**
    * Check if the file size it's correct
    *
    * @param Model $model Model using this behavior
    * @param array $check File to be checked
    * @param array $size  The max size allowed
    *
    * @access public
    * @return bool Return true in case of valid and false in case of invalid
    */
    public function size(Model $model, $check, $size)
    {
        if ($this->shouldValidate($model, __METHOD__, $check)) {
            return true;
        }

        $check = array_shift($check);

        return $size >= $check['size'];
    }

    /**
    * Check if the image fits within given dimensions
    *
    * @param Model $model  Model using this behavior
    * @param array $check  File to be checked
    * @param int   $width  Maximum width in pixels
    * @param int   $height Maximum height in pixels
    *
    * @access public
    * @return  bool Return true if image fits withing given dimensions
    */
    public function maxDimensions(Model $model, $check, $width, $height)
    {
        if ($this->shouldValidate($model, __METHOD__, $check)) {
            return true;
        }

        $check = array_shift($check);

        if (isset($check['tmp_name']) && file_exists($check['tmp_name'])) {
            $info = getimagesize($check['tmp_name']);

            return ($info && $info[0] <= $width && $info[1] <= $height);
        }

        return false;
    }

    /**
     * Check if the image fits within given dimensions
     *
     * @param Model $model  Model using this behavior
     * @param mixed $check  File to be checked
     * @param mixed $width  Minimum width in pixels
     * @param mixed $height Minimum height in pixels
     *
     * @access public
     * @return bool Return true if image fits within given dimensions
     */
    public function minDimensions(Model $model, $check, $width, $height)
    {
        if ($this->shouldValidate($model, __METHOD__, $check)) {
            return true;
        }

        $check = array_shift($check);

        if (isset($check['tmp_name']) && file_exists($check['tmp_name'])) {
            $info = getimagesize($check['tmp_name']);

            return ($info && $info[0] >= $width && $info[1] >= $height);
        }

        return false;
    }

    /**
     * Return the mime type of the given file
     *
     * @param Model  $model Model using this behavior
     * @param string $file  Path of file
     *
     * @access public
     * @return string Mimetype
     */
    public function getFileMime(Model $model, $file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $info  = finfo_file($finfo, $file);

        return $info;
    }

    /**
     * Get the file extension
     *
     * @param string $file File to be checked
     *
     * @return string File extension
     * @access public
     */
    public function getFileExtension(Model $model, $file)
    {
        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }

    /**
     * Return the upload folder that was set for the given type
     *
     * @param Model  $model Model using this behavior
     * @param string $type  Type of the file upload
     *
     * @return string Path for the upload folder
     * @access public
     */
    public function getUploadFolder($model, $type)
    {
        return APP . str_replace(
            '{DS}',
            DS,
            $this->config[$model->alias][$type]['dir']
        ) . DS;
    }

    /**
     * Return if the folder is writable
     *
     * @param string $dir Path of folder
     *
     * @throws CakeException case the folder is not writable
     *
     * @return bool return if the folder is writable
     * @access public
     */
    public function isWritable($dir)
    {
        if (is_dir($dir) && is_writable($dir)) {
            return true;
        }

        throw new CakeException(sprintf('Folder is not writable: %s',  $dir));
    }

    /**
     * afterSave is called after a model is saved.
     *
     * @param Model   $model   Model using this behavior
     * @param boolean $created True if this save created a new record
     *
     * @return boolean
     * @access public
     */
    public function afterSave(Model $model, $created)
    {
        parent::afterSave($model, $created);

        foreach ($this->types[$model->alias] as $type) {

            $data = $model->data;

            //set multiple as false by standard
            $this->multiple[$model->alias] = false;

            if ($this->_isMultiple($model, $type)) {
                $this->multiple[$model->alias] = true;

                $check = isset($data[$model->alias])
                    && isset($data[$model->alias][$type])
                    && is_array($data[$model->alias][$type]);
            } else {
                $check = isset($data[$model->alias][$type]['tmp_name'])
                    && !empty($data[$model->alias][$type]['tmp_name']);
            }

            //case has the file update :)
            if ($check) {
                if (isset($this->multiple[$model->alias]) && $this->multiple[$model->alias]) {
                    foreach ($data[$model->alias][$type] as $index => $value) {
                        $this->saveFile($model, $type, $index);
                    }
                } else {
                    $this->saveFile($model, $type);
                }
            }
        }
    }

    /**
     * Before delete is called before any delete occurs on the attached model,
     * but after the model's beforeDelete is called.
     * Returning false from a beforeDelete will abort the delete.
     *
     * @param Model   $model   Model using this behavior
     * @param boolean $cascade If true records that depend on this record will also be deleted
     *
     * @return mixed False if the operation should abort. Any other result will continue.
     * @access public
     */
    public function beforeDelete(Model $model, $cascade = true)
    {
        if ($cascade = true) {
            foreach ($this->types[$model->alias] as $type) {
                $className = 'Attachment'. Inflector::camelize($type);

                $attachments = $model->{$className}->find(
                    'all',
                    array(
                        'conditions' => array(
                            'model' => $model->alias,
                            'foreign_key' => $model->id,
                        ),
                    )
                );

                foreach ($attachments as $attach) {
                    $this->deleteAllFiles($model, $attach);
                }
            }
        }

        return $cascade;
    }

    /**
     * Save the given type of file
     *
     * @param Model  $model Model using this behavior
     * @param string $type  Type of the file upload
     * @param int    $index Case is multiple send the index of data
     *
     * @throws CakeException case the file is not one image
     *
     * @return void
     * @access public
     */
    public function saveFile(Model $model, $type, $index = null)
    {
        $uploadData = $model->data[$model->alias][$type];

        if (!is_null($index)) {
            $uploadData = $uploadData[$index];
        }

        if (!isset($uploadData['tmp_name']) || empty($uploadData['tmp_name'])) {
            return;
        }

        $file   = $model->generateName($type, $index);
        $attach = $this->saveAttachment($model, $type, $file);

        if (!empty($uploadData['tmp_name'])) {

            //move file
            copy($uploadData['tmp_name'], $file);
            $this->deleteFile($uploadData['tmp_name']);

            if (isset($this->config[$model->alias][$type]['thumbs'])) {
                $info = getimagesize($file);
                if (!$info) {
                    throw new CakeException(
                        sprintf('The file %s is not an image', $file)
                    );
                }
                
                //generate thumbs
                $this->createThumbs($model, $type, $file);
            }
        }
    }
    /**
    * Save the given type of file
    *
    * @param Model $model      Model using this behavior
    * @param mixed $attachment Attachment to be deleted
    *
    * @return void
    */
    public function deleteAllFiles(Model $model, $attachment)
    {
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

    /**
     * Delete the specific given file
     *
     * @param string $file File to be checked
     *
     * @return bool true case was deleted with success
     */
    public function deleteFile($file)
    {
        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }

    /**
     * Insert attachment into the database
     *
     * @param Model  $model    Model using this behavior
     * @param string $type     Type of the file upload
     * @param string $filename Filename to be saved
     *
     * @return void
     */
    protected function saveAttachment(
        Model $model,
        $type,
        $filename
    ) {
        $className = 'Attachment'. Inflector::camelize($type);

        $attachment = false;

        $attachment = $model->{$className}->find(
            'first',
            array(
                'conditions' => array(
                    'foreign_key' => $model->id,
                    'model' => $model->alias,
                    'type' => $type,
                ),
            )
        );

        $data = array(
            $className => array(
                'model' => $model->alias,
                'foreign_key' => $model->id,
                'filename' => basename($filename),
                'type' => $type,
            ),
        );

        if (!empty($attachment) && $attachment !== false) {
            $this->deleteAllFiles($model, $attachment);
            $data[$className]['id'] = $attachment[$className]['id'];
        } else {
            $model->{$className}->create();
        }

        $model->data += $model->{$className}->save($data);
        
    }

    /**
     * Generate an unique name to save the file
     *
     * @param Model  $model Model using this behavior
     * @param string $type  Type of the file upload
     * @param int    $index Case is multiple send the index of data
     *
     * @return string Generated name
     * @access public
     */
    public function generateName(Model $model, $type, $index = null)
    {
        $dir = $this->getUploadFolder($model, $type);

        if (is_null($index)) {
            $extension = $this->getFileExtension(
                $model,
                $model->data[$model->alias][$type]['name']
            );
        } else {
            $extension = $this->getFileExtension(
                $model,
                $model->data[$model->alias][$type][$index]['name']
            );
        }

        if (!is_null($index)) {
            return $dir 
                . $type 
                . '_' 
                . $index 
                . '_' 
                . $model->id 
                . '.' 
                . $extension;
        }

        return $dir . $type . '_' . $model->id  . '.' . $extension;
    }

    /**
     * Create thumbs for the given image based in the config
     * defined in the model
     *
     * @param Model  $model Model using this behavior
     * @param string $type  Type of the file upload
     * @param string $file  Image file
     *
     * @return void
     * @access protected
     */
    protected function createThumbs($model, $type, $file)
    {
        $imagine   = $this->_getImagine();
        $image     = $imagine->open($file);
        $thumbName = basename($file);
        $thumbs    = $this->config[$model->alias][$type]['thumbs'];

        foreach ($thumbs as $key => $values) {
            if (!isset($values['crop'])) {
                $values['crop'] = false;
            }

            $this->_generateThumb(
                array(
                    'name' => str_replace(
                        $thumbName,
                        $key . '.' . $thumbName,
                        $file
                    ),
                    'w' => $values['w'],
                    'h' => $values['h'],
                ),
                $image,
                $values['crop']
            );
        }
    }

    /**
     * Create a thumb for the given image file based in the parameters passed
     *
     * @param string $file   Image file
     * @param string $name   Name of the thumb
     * @param float  $width  Width of thumb
     * @param float  $height Height of thumb
     * @param bool   $crop   Crop the image
     *
     * @return void
     * @access public
     */
    public function createThumb($file, $name, $width, $height, $crop = false)
    {
        $imagine = $this->getImagine();
        $image   = $imagine->open($file);

        $this->__generateThumb(
            array(
                'w' => $width,
                'h' => $height,
                'name' => $name,
            ),
            $image,
            $crop
        );
    }


    /**
     * Load the imagine library
     *
     * @throws CakeException
     *
     * @return \Imagine\Gd\Imagine
     * @access private
     */
    private function _getImagine()
    {
        if (!interface_exists('Imagine\Image\ImageInterface')) {
            if (file_exists(VENDORS . 'imagine.phar')) {
                include_once 'phar://' . VENDORS . 'imagine.phar';
            } else {
                throw new CakeException(
                    sprintf(
                        'Download imagine.phar: %s, and extract into vendor: %s',
                        self::IMAGINE_URL,
                        VENDORS
                    )
                );
            }
        }

        return new \Imagine\Gd\Imagine();
    }

    /**
     * Generate the thumb
     *
     * @param mixed  $thumb 'width', 'height' and 'name'
     * @param string $image image file
     * @param bool   $crop  Crop the image
     *
     * @return void
     * @access private
     */
    private function _generateThumb($thumb, $image, $crop = false)
    {
        if ($crop) {
            $mode =  Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
        } else {
            $mode = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
        }

        $thumbnail = $image->thumbnail(
            new Imagine\Image\Box(
                $thumb['w'],
                $thumb['h']
            ),
            $mode
        );

        $thumbnail->save($thumb['name']);
    }

}
