<?php
App::uses('UploadBehavior', 'Attach.Model/Behavior');
App::uses('Folder', 'Utility');

class Post extends CakeTestModel {

	public $actsAs = array(
		'Attach.Upload' => array(
			'home' => array(
				'dir' => 'tmp{DS}Attach{DS}',
				'thumbs' => array(
					'thumb' => array(
						'w' => 100,
						'h' => 100,
						'crop' => true
					)
				)
			)
		)
	);

}

class Article extends CakeTestModel {

	public $actsAs = array(
		'Attach.Upload' => array(
			'thumb' => array(
				'dir' => 'tmp{DS}Attach{DS}',
				'thumbs' => array(
					'thumb' => array(
						'w' => 100,
						'h' => 100,
						'crop' => true
					)
				)
			)
		)
	);

}

class UploadBehaviorTest extends CakeTestCase {

/**
 * Folder object
 * 
 * @var Folder
 */
	public $folder;

	public $fixtures = array(
		'plugin.attach.attachment',
		'core.article'
	);

	public function setUp() {
		parent::setUp();
		$this->folder = new Folder();
		$this->folder->create(APP . 'tmp' . DS . 'Attach');
		$this->Article = ClassRegistry::init('Article');
	}

	protected function getConfig() {
		return array(
			'home' => array(
				'dir' => 'tmp{DS}Attach{DS}',
				'thumbs' => array(
					'thumb' => array(
						'w' => 100,
						'h' => 100,
						'crop' => true
					)
				)
			)
		);  
	}

	public function tearDown() {
		unset($this->Article);
		$this->folder->delete(APP . 'tmp' . DS . 'Attach');
		parent::tearDown();
	}

	public function testSetup() {
		$this->Article->
	}

	public function testUpload() {

	}

	public function testIfPostFileDataIsEmpty() {

	}

	public function testIfFileIsRequired() {

	}

	public function testValidateExtension() {

	}

	public function testValidateMime() {

	}

	public function testValidateSize() {

	}

	public function testValidateMaxDimensions() {

	}

	public function testValidateMinDimensions() {

	}

	public function testFileMime() {

	}

	public function testFileExtension() {

	}

	public function testUploadFolder() {

	}

	public function testIfDirectoryIsWritable() {

	}

	public function testGenerateName() {

	}

	public function testThumbCreation() {

	}

}