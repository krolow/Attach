<?php
/**
 * UploadBehavior
 * 
 * PHP Version 5.3+
 *
 * @category Plugin
 * @package  Attachment.Behavior
 * @author   Vinícius Krolow <krolow@gmail.com>
 * @license  GNU GENERAL PUBLIC LICENSE
 * @link     https://github.com/krolow/Attach
 */

App::uses('Attachment', 'Attach.Model');

/**
 * UploadBehavior
 * 
 * @category Plugin
 * @package  Attachment.Behavior
 * @author   Vinícius Krolow <krolow@gmail.com>
 * @license  GNU GENERAL PUBLIC LICENSE
 * @link     https://github.com/krolow/Attach
 */
class UploadBehavior extends ModelBehavior
{
    /**
     * Setup method
     * 
     * @param Model $model  Model related
     * @param Array $config Config comment
     * 
     * @todo Really doc this method
     * @return void
     */
    public function setup(Model $model, $config = array())
    {
        $this->config[$model->alias] = $config;
        
        $this->types[$model->alias] = array_keys(
            $this->config[$model->alias]
        );

        foreach ($this->types[$model->alias] as $index => $type) {
            $folder = $this->getUploadFolder($model, $type);
            $this->isWritable($this->getUploadFolder($model, $type));
            $this->setRelation($model, $this->types[$model->alias][$index]);
        }
    }

    /**
     * setRelation method
     * 
     * @param Model  $model Model related
     * @param String $type  Config comment
     * 
     * @todo Really doc this method
     * @return void
     */
    public function setRelation(Model $model, $type)
    {
        $type     = Inflector::camelize($type);
        $relation = 'hasOne';

        //case is defined multiple is a hasMany
        if (isset($this->config[$model->alias][$type]['multiple'])
            && $this->config[$model->alias][$type]['multiple'] == true
        ) {
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
     * @param Model $model      Model related
     * @param Array $check      Array of data from the file that is been
     * checking
     * @param Array $extensions Extensions comment
     * 
     * @return bool Return true in case of valid and false in case of invalid
     * @access public
     * @todo Update documentation 
     */
    public function extension($model, $check, $extensions)
    {
        $check = array_shift($check);
        if (isset($check['name'])) {
            return in_array(
                $this->getFileExtension($check['name']),
                $extensions
            );
        }

        return false;
    }

    /**
     * Check if the mime type it's correct
     *
     * @param Model $model Model related
     * @param Array $check Array of data from the file that is been checking
     * @param Array $mimes Mimes comment
     * 
     * @return bool Return true in case of valid and false in case of invalid
     * @access public
     */
    public function mime($model, $check, $mimes)
    {
        $check = array_shift($check);

        if (isset($check['tmp_name']) && is_file($check['tmp_name'])) {
            $info = $this->getFileMime($model, $check['tmp_name']);

            return in_array($info, $mimes);
        }

        return false;
    }

    /**
     * size method
     * 
     * @param Model  $model Model related
     * @param String $check Check comment
     * @param String $size  Size comment
     * 
     * @todo Really doc this method
     * @return void
     */ 
    public function size($model, $check, $size)
    {
        $check = array_shift($check);
        return $size >= $check['size'];
    }

    /**
     * Check if the image fits within given dimensions
     *
     * @param Model   $model  Model related
     * @param Array   $check  Array of data from the file that is been checked
     * @param Integer $width  Maximum width in pixels
     * @param Integer $height Maximum height in pixels
     * 
     * @return bool Return true if image fits withing given dimensions
     * @access public
     */
    public function maxDimensions($model, $check, $width, $height)
    {
        $check = array_shift($check);

        if (isset($check['tmp_name']) && is_file($check['tmp_name'])) {
            $info = getimagesize($check['tmp_name']);

            return ($info && $info[0] <= $width && $info[1] <= $height);
        }

        return false;
    }

    /**
     * getFileMime method
     * 
     * @param Model  $model Model related
     * @param String $file  Check comment
     * 
     * @todo Really doc this method
     * @return String
     */ 
    public function getFileMime($model, $file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $info  = finfo_file($finfo, $file);

        return $info;
    }

    /**
     * Check if the mime type it's correct
     *
     * @param Array $filename Array of data from the file that is been checking
     * 
     * @todo Really doc this method
     * @return Boolean Return true in case of valid and false otherwise
     * @access protected
     */
    public function getFileExtension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Return the upload folder that was set
     *
     * @param Model  $model Model related
     * @param String $type  Check comment
     * 
     * @todo Really doc this method
     * @return string Path for the upload folder
     * @access public
     */
    public function getUploadFolder($model, $type)
    {
        $dir = $this->config[$model->alias][$type]['dir'];
        return APP . str_replace('{DS}', DS, $dir) . DS;
    }

    /**
     * Check if a dir is writable
     *
     * @param String $dir Absolut folder's path
     * 
     * @access public
     * @return Boolean
     */
    public function isWritable($dir)
    {
        if (is_dir($dir) && is_writable($dir)) {
            return true;
        }

        throw new Exception('Folder is not writable: ' .  $dir);
    }

    /**
     * afterSave callback
     *
     * @param Model   $model   Model related
     * @param boolean $created True if this is a new record
     * 
     * @access public
     * @return void
     */
    public function afterSave(Model $model, $created)
    {
        foreach ($this->types[$model->alias] as $type) {
            //set multiple as false by standard
            $multiple = false;

            if (isset($this->config[$model->alias][$type]['multiple'])
                && $this->config[$model->alias][$type]['multiple'] === true
            ) {
                $multiple = true;
                $check    = is_array($model->data[$model->alias][$type]);
            } else {
                $check = isset($model->data[$model->alias][$type]['tmp_name'])
                    && !empty($model->data[$model->alias][$type]['tmp_name']);
            }

            //case has the file update :)
            if ($check) {
                if ($multiple) {
                    
                    $types = $model->data[$model->alias][$type];
                    
                    foreach ($types as $index => $value) {
                        $this->saveFile($model, $type, $index);
                    }

                } else {
                    $this->saveFile($model, $type);
                }
            }
        }
    }

    /**
     * beforeDelete callback
     *
     * @param Model   $model   Model related
     * @param Boolean $cascade If true records that depend on this record will 
     * also be deleted
     * 
     * @return Boolean
     */
    public function beforeDelete($model, $cascade = true)
    {
        if ($cascade = true) {

            foreach ($this->types[$model->alias] as $type) {

                $className = 'Attachment'. Inflector::camelize($type);

                $attachments = $model->{$className}->find(
                    'all', array(
                        'conditions' => array(
                            'model' => $model->name,
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
     * saveFile
     *
     * @param Model   $model Related model
     * @param String  $type  Relation'n type
     * @param Integer $index Index comment
     * 
     * @todo Really doc method
     * @return void
     */
    public function saveFile(Model $model, $type, $index = null)
    {
        $uploadData = $model->data[$model->alias][$type];

        if (!is_null($index)) {
            $uploadData = $uploadData[$index];
        }

        $file   = $this->generateName($model, $type, $index);
        $attach = $this->saveAttachment($model, $type, $file);

        //move file
        copy($uploadData['tmp_name'], $file);
        @unlink($uploadData['tmp_name']);

        if (isset($this->config[$model->alias][$type]['thumbs'])) {

            $info = getimagesize($file);

            if (!$info) {
                throw new CakeException(
                    sprintf('The file %s is not an image', $file)
                );
            }
            $this->createThumbs($model, $file, $type);
        }
    }

    /**
     * deleteAllFiles
     *
     * @param Model $model      Related model
     * @param Array $attachment Attachment comment
     * 
     * @todo Really doc method
     * @return void
     */
    public function deleteAllFiles($model, $attachment)
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
     * deleteFile
     *
     * @param String $filename Filename comment
     * 
     * @return Boolean
     */
    public function deleteFile($filename)
    {
        if (file_exists($filename)) {
            return unlink($filename);
        }

        return false;
    }

    /**
     * saveAttachment
     *
     * @param Model  $model    Related model
     * @param Array  $type     Type comment
     * @param String $filename Filename comment
     * @param Mixed  $edit     Edit comment
     * 
     * @todo Really doc method
     * @return void
     */
    protected function saveAttachment(
        Model $model, $type, $filename, $edit = null
    ) {
        $className = 'Attachment'. Inflector::camelize($type);

        $attachment = $model->{$className}->find(
            'first',
            array(
                'conditions' => array(
                    'foreign_key' => $model->id,
                    'model' => $model->alias,
                    'type' => $type,
                    'filename' => $filename,
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

        if ($attachment) {
            $this->deleteAllFiles($model, $attachment);
            $data[$className]['id'] = $attachment[$className]['id'];
        } else {
            $model->{$className}->create();
        }

        $model->data += $model->{$className}->save($data);
    }

    /**
     * generateName
     *
     * @param Model   $model Related model
     * @param Array   $type  Type comment
     * @param Integer $index Index comment
     * 
     * @todo Really doc method
     * @return String
     */
    public function generateName(Model $model, $type, $index = null)
    {
        $dir = $this->getUploadFolder($model, $type);

        if (is_null($index)) {
            $extension = $this->getFileExtension(
                $model->data[$model->alias][$type]['name']
            );
        } else {
            $extension = $this->getFileExtension(
                $model->data[$model->alias][$type][$index]['name']
            );
        }

        if (!is_null($index)) {
            return $dir . $type . '_' . $index . '_' . $model->id .
                   '.' . $extension;
        }

        return $dir . $type . '_' . $model->id . '.' . $extension;
    }

    /**
     * createThumb
     *
     * @param Model  $model Related model
     * @param String $file  File comment
     * @param Array  $type  Type comment
     * 
     * @todo Really doc method and remove
     * @return void
     */
    public function createThumbs($model, $file, $type)
    {
        $imagine = $this->_getImagine();
        $image   = $imagine->open($file);

        $thumbName = basename($file);
        $types     = $this->config[$model->alias][$type]['thumbs'];

        foreach ($types as $key => $values) {

            $this->_generateThumb(
                array(
                    'name' => str_replace(
                        $thumbName,
                        $key . '.' . $thumbName, $file
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
     * _getImagine method
     *
     * @todo Really doc method, use submodule instead phar
     * @return Imagine
     */
    private function _getImagine()
    {
        if (!interface_exists('Imagine\Image\ImageInterface')) {
            if (is_file(VENDORS . 'imagine.phar')) {
                include_once 'phar://' . VENDORS . 'imagine.phar';
            } else {

                $textException = 'You should add in your vendors folder %s, 
                                  the imagine.phar, you can download here: 
                                  https://github.com/avalanche123/Imagine';
                
                throw new CakeException(sprintf($textException, VENDORS));
            }
        }

        return new \Imagine\Gd\Imagine();
    }

    /**
     * createThumb
     *
     * @param String  $filename Filename comment
     * @param String  $name     Name comment
     * @param Integer $width    Width comment
     * @param Integer $height   Height comment
     * @param Boolean $crop     True when image must to be cropped
     * 
     * @todo Really doc method
     * @return void
     */
    public function createThumb(
        $filename, $name, $width, $height, $crop = false
    ) {
        $imagine = $this->_getImagine();
        $image   = $imagine->open($filename);

        $this->_generateThumb(
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
     * Hold thumbs generation
     *
     * @param Array   $thumb Thumb comment
     * @param Object  $image Image comment
     * @param Boolean $crop  True when image must to be cropped
     * 
     * @todo Really doc method and remove __ prefix
     * @return void
     */
    private function _generateThumb($thumb, $image, $crop = false)
    {
        if ($crop) {
            $mode =  Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
        } else {
            $mode = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
        }

        $thumbnail = $image->thumbnail(
            new Imagine\Image\Box($thumb['w'], $thumb['h']),
            $mode
        );

        $thumbnail->save($thumb['name']);
    }
}

