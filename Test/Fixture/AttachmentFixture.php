<?php
class AttachmentFixture extends CakeTestFixture {

    public $name = 'Attachment';

    public $fields = array(
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

    public $records = array(
        array(
            'id' => 1,
            'filename' => 'home_1.jpg',
            'model' => 'Campaign',
            'foreign_key' => 1,
            'type' => 'home'
        )
    );

}