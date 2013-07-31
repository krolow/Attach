<?php
class PostFixture extends CakeTestFixture {

	public $name = 'Post';

	public $fields = array(
		'id' => array(
			'type' => 'integer',
			'null' => false,
			'default' => null,
			'key' => 'primary',
			'collate' => null,
			'comment' => ''
		),
		'name' => array(
			'type' => 'string',
			'null' => false,
			'default' => null,
			'key' => 'primary',
			'collate' => null,
			'comment' => ''
		),
	);
}