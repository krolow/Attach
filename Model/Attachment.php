<?php
/**
 * Attachment Model
 * 
 * PHP Version 5.3+
 *
 * @version       1.0
 * @link          https://github.com/krolow/Attach
 * @package       Attach.Model.Attachment
 * @author        Vinícius Krolow <krolow@gmail.com>
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('AppModel', 'Model');

/**
 * Attachment Model
 * 
 * @category Plugin
 * @package  Attachment.Model
 * @author   Vinícius Krolow <krolow@gmail.com>
 * @license  GNU GENERAL PUBLIC LICENSE
 * @link     https://github.com/krolow/Attach
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
                'message' => 'Filename cannot be empty',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 
                //'update' operations
            ),
        ),
        'model' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 
                //'update' operations
            ),
        ),
        'foreign_key' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 
                //'update' operations
            ),
        ),
    );
}

