<?php
App::uses('Attach.Upload', 'Model/Behavior');

class UploadBehaviorTest extends CakeTestCase {

    public $fixtures = array(
        'plugin.attach.attachment',
        'core.article'
    );

    public function setUp() {
        parent::setUp();
        mkdir(APP . 'tmp' . DS . 'Attach', 0777, true);
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
        rmdir(APP . 'tmp' . DS . 'Attach');
        parent::tearDown();
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