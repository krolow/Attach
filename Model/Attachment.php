<?php
/**
* Attachment model
*
*
* PHP 5.3
*
*
* Licensed under The MIT License
* Redistributions of files must retain the above copyright notice.
*
* @link          https://github.com/krolow/Attach
* @package       Attach.Model.UploadBehavior
* @author		 Vinícius Krolow <krolow@gmail.com>
* @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
*/
App::uses('AppModel', 'Model');

/**
 * Attachment Model
 *
 */
class Attachment extends AppModel
{
    /**
     * Display field
     *
     * @var string
     */
	public $displayField = 'id';

    /**
     * Validation rules
     *
     * @var array
     */
	public $validate = array(
		'filename' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'model' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'foreign_key' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

}
