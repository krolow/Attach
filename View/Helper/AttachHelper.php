<?php
App::uses('AppHelper', 'Helper');

/**
 * Attachment Model
 * 
 * PHP Version 5.3+
 *
 * @version       1.0
 * @link          https://github.com/krolow/Attach
 * @package       Attach.View.Helper
 * @author        Vinícius Krolow <krolow@gmail.com>
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class AttachHelper extends AppHelper {

    public $helpers = array(
        'Html'
    );

    public function image($attach, $type = null, $options = array()) {
        if (!isset($attach['model'])) {
            throw new Exception('It looks that you are trying to render not one attachment');
        }

        if (!class_exists($attach['model'])) {
            throw new Exception('Seems that there is no class for the given attach');   
        }

        $model = ClassRegistry::init($attach['model']);
        $path = str_replace(
            WWW_ROOT, 
            '/', 
            $model->getUploadFolder($attach['type'])
        );

        if (!is_null($type)) {
            $type = $type . '.';
        }

        return $this->Html->image($path . $type . $attach['filename']);
    }

}