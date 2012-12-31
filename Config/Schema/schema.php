<?php
/**
 * AttachSchema
 * 
 * PHP Version 5.3+
 *
 * @version       1.0
 * @link          https://github.com/krolow/Attach
 * @package       Attach.Config.Schema
 * @author        Vinícius Krolow <krolow@gmail.com>
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class AttachSchema extends CakeSchema
{
    /**
     * Before callback
     *
     * @param Array $event Event
     * 
     * @return Boolean
     */
    public function before($event = array())
    {
        return true;
    }

    /**
     * After callback
     *
     * @param Array $event Event
     * 
     * @return Boolean
     */
    public function after($event = array())
    {
    
    }

    public $attachments = array(

        'id' => array(
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'key' => 'primary',
            'collate' => null,
            'comment' => ''
        ),

        'filename' => array(
            'type' => 'string',
            'null' => false,
            'default' => null,
            'length' => 150,
            'collate' => 'utf8_general_ci',
            'comment' => '',
            'charset' => 'utf8'
        ),

        'model' => array(
            'type' => 'string',
            'null' => false,
            'default' => null,
            'length' => 150,
            'collate' => 'utf8_general_ci',
            'comment' => '',
            'charset' => 'utf8'
        ),

        'foreign_key' => array(
            'type' => 'integer',
            'null' => false,
            'default' => null,
            'collate' => null,
            'comment' => ''
        ),

        'type' => array(
            'type' => 'string',
            'null' => false,
            'default' => null,
            'length' => 100,
            'collate' => 'utf8_general_ci',
            'comment' => '',
            'charset' => 'utf8'
        ),

        'indexes' => array(
            'PRIMARY' => array('column' => 'id', 'unique' => 1)
        ),

        'tableParameters' => array(
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
            'engine' => 'InnoDB'
        )
    );
}
