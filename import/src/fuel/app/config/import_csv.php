<?php

return [
	'upload' => [
		'path' => DOCROOT . 'uploads',
		'ext_whitelist' => ['csv'],
		'max_size' => 10240, // 10MB 
	],
	'csv' => [
		'delimiter' => ';',
		'enclosure' => '"',
		'escape' => '\\',
		'columns' => [
			'shopId' => 'ログインID',
			'password' => 'パスワード',
			'confirm_password' => 'パスワード確認用',
		],
		'from_encoding' => 'UTF-8',
		'to_encoding' => 'UTF-8'
	],
];
