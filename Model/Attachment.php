<?php
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
