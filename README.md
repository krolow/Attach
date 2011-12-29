Attach
======

Attach is a CakePHP 2.0 plugin, to make the upload easily.

To generate thumbs attach uses Imagine library: http://github.com/avalanche123/Imagine


Example:


App::uses('AppModel', 'Model');

class Media extends AppModel {

	public $validate = array(
		'image' => array(
			'extension' => array(
				'rule' => array(
					'extension', array(
						'jpg',
						'jpeg',		'image' => array(
			'extension' => array(
				'rule' => array(
					'extension', array(
						'jpg',
						'jpeg',
						'bmp',
						'gif',
						'png',
						'jpg'
					)
				),
				'message' => 'File extension is not supported',
				'on' => 'create'
			),
			'mime' => array(
				'rule' => array('mime', array(
					'image/jpeg',
					'image/pjpeg',
					'image/bmp',
					'image/x-ms-bmp',
					'image/gif',
					'image/png'
				)),
				'on' => 'create'
			),
			'size' => array(
				'rule' => array('size', 2097152),
				'on' => 'create'
			)
		),
						'bmp',
						'gif',
						'png',
						'jpg'
					)
				),
				'message' => 'File extension is not supported',
				'on' => 'create'
			),
			'mime' => array(
				'rule' => array('mime', array(
					'image/jpeg',
					'image/pjpeg',
					'image/bmp',
					'image/x-ms-bmp',
					'image/gif',
					'image/png'
				)),
				'on' => 'create'
			),
			'size' => array(
				'rule' => array('size', 2097152),
				'on' => 'create'
			)
		),
	);

	public $actsAs = array(
		'Attach.Upload' => array(
			'swf' => array(
				'dir' => 'webroot{DS}uploads{DS}media{DS}swf'
			),
			'image' => array(
				'dir' => 'webroot{DS}uploads{DS}media{DS}image',
				'thumbs' => array(
					'thumb' => array(
						'w' => 190,
						'h' => 158,
						'crop' => true,
					),
				),
			),
			'zip' => array(
				'dir' => 'webroot{DS}uploads{DS}media{DS}zip'
			),
		),
	);

