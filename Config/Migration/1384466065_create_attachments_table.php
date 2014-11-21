<?php
class CreateAttachmentsTable extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = '';

/**
 * Actions to be performed
 *
 * @var array $migration
 * @access public
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'attachments' => array(
					'id' => array(
						'type' => 'integer',
						'null' => false,
						'default' => null,
						'key' => 'primary'
					),
					'filename' => array(
						'type' => 'string',
						'null' => false,
						'length' => 150
					),
					'model' => array(
						'type' => 'string',
						'null' => false,
						'length' => 150
					),
					'foreign_key' => array(
						'type' => 'integer',
						'null' => false
					),
					'type' => array(
						'type' => 'string',
						'null' => false,
						'length' => 100
					),
					'indexes' => array(
						'PRIMARY' => array(
							'column' => 'id',
							'unique' => 1
						),
					),
					'tableParameters' => array(
						'charset' => 'latin1',
						'collate' => 'latin1_swedish_ci',
						'engine' => 'InnoDB'
					),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'attachments'
			),
		),
	);


/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function after($direction) {
		return true;
	}
}
