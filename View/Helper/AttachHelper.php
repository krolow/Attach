<?php
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

/**
 * Load Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Html'
	);

/**
 * Render image
 *
 * @throws RunTimeException When no model is set in data or class does not exists
 *
 * @return string
 */
	public function image($attach, $type = null, $options = array()) {
		if (!isset($attach['model'])) {
			throw new RunTimeException('Seems that the given attach is not really from the Attachment model');
		}

		if (!class_exists($attach['model'])) {
			throw new RunTimeException('Seems that there is no class for the given attach');
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

		return $this->Html->image($path . $type . $attach['filename'], $options);
	}

}
