# Attach 1.0

Attach is a CakePHP 2.0 Plugin, that makes uploads a simple task!

Attach contains a behavior that does everything for you, uploads your file, and resizes your images.

## Requirements

- PHP 5.3 or >
- CakePHP 2.0 or >

## Installation
- Clone from github : in your app directory type `git clone git@github.com:krolow/Attach.git Plugin/Attach`
- Download an archive from github and extract it in `app/Plugin/Attach`

* If you require thumbnails for image generation, you should install the dependencies using composer, **and make sure to call the autoload of composer in your CakePHP application**


## Usage
In a model that needs uploads, replace the class declaration with something similar to the following:


It's important to remember that your model class can have your own fields, and it will have a extra relation with Attachment model with the fields that are upload.

```php
<?php
	App::uses('AppModel', 'Model');

	class Media extends AppModel {

		public $validate = array(
			'image' => array(
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
			'swf' => array(
				'extension' => array(
					'rule' => array(
						'extension', array(
							'swf',
						)
					),
					'message' => 'File extension is not supported',
					'on' => 'create'
				),
				'mime' => array(
					'rule' => array('mime', array(
						'application/x-shockwave-flash',
					)),
					'on' => 'create'
				),
				'size' => array(
					'rule' => array('size', 53687091200),
					'on' => 'create'
				)
			),
			'zip' => array(
				'extension' => array(
					'rule' => array(
						'extension', array(
							'zip',
						)
					),
					'message' => 'File extension is not supported',
					'on' => 'create'
				),
				'mime' => array(
					'rule' => array('mime', array(
						'application/zip',
						'multipart/x-zip'
					)),
					'on' => 'create'
				),
				'size' => array(
					'rule' => array('size', 53687091200),
					'on' => 'create'
				)
			),
		);

		public $actsAs = array(
			'Attach.Upload' => array(
				'Attach.type' => 'Imagick', //you can choose btw Imagick or Gd to handle the thumbnails, in case you do not pass that default is GD
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
```

You also must create one table in your database:

You can do this with a schema:

```
cake.php schema create --plugin Attach
```


Or you can do it with SQL:
```sql
CREATE TABLE  `attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(150) NOT NULL,
  `model` varchar(150) NOT NULL,
  `foreign_key` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `size` int(11) NOT NULL,
  `original_name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
```

Create your upload view, make sure it's a multipart/form-data form, and that the filename field is of the type 'file':

```php
<?php
		echo $this->Form->create('Media', array('type' => 'file'));
		echo $this->Form->input('name');
		echo $this->Form->input('image', array('type' => 'file'));
		echo $this->Form->input('swf', array('type' => 'file'));
		echo $this->Form->input('zip', array('type' => 'file'));
		echo $this->Form->input('status');
		echo $this->Form->end(__('Submit'));
```



Attach automatically creates the relationship with the model Attachment, for each type that you define:

```php
		var_dump($this->Media->AttachmentImage);
		var_dump($this->Media->AttachmentSwf);
		var_dump($this->Media->AttachmentZip);
```

It will be always "Attachment" plus the type!

## License

Licensed under <a href="http://krolow.mit-license.org/">The MIT License</a>
Redistributions of files must retain the above copyright notice.

## Author

Vinícius Krolow - krolow[at]gmail.com
