<?php
class AddingMd5OfFile extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'adding_md5_of_file';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
        'up' => array(
            'create_field' => array(
                'attachments' => array(
                    'md5' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_bin', 'charset' => 'utf8'),
                )
            ),
        ),
        'down' => array(
            'drop_field' => array(
                'attachments' => array(
                    'md5',
                )
            )
        ),
	);

/**
 * Before migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function after($direction) {
		return true;
	}
}
