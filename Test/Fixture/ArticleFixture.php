<?php
class ArticleFixture extends CakeTestFixture {

	public $name = 'Article';

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