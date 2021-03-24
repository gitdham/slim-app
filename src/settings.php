<?php
return [
	'settings' => [
		'displayErrorDetails' => true, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header

		// Renderer settings
		'renderer' => [
			'template_path' => __DIR__ . '/../templates/',
		],

		// Monolog settings
		'logger' => [
			'name' => 'slim-app',
			'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],

		// DB settings
		'database' => [
			'DB_HOST' => 'localhost',
			'DB_USER' => 'root',
			'DB_PASSWORD' => '',
			'DB_NAME' => 'dev_sistem_stok',
			'DB_PORT' => '3306'
		],

		// JWT
		'jwt_secret' => [
			'ACCESS_TOKEN_SECRET' => '627f6ccff17ac7b9c8f2688ebbd4befe860b286453b8c5a44a7117b553ad0efa8451d3be0631152f11e9bde07f193012a4867d1c80d78a624bd6d65c5f088c45',
			'REFRESH_TOKEN_SECRET' => 'cb3338dc681da7fe4ac86360df2cc49fadf111dffe4ad74a58819046742ce3ac8d963b9c1152313629e19a3c36bb8705dba25659e39c08d76e4a92a0c62e5fd5'
		],

		// Mail
		// 'mail' => [
		// 	'SMTP_HOST' => 'tls://smtp.gmail.com',
		// 	'MAIL_USERNAME' => 'sibarang999@gmail.com',
		// 	'MAIL_PASSWORD' => 'pasmodbatununggal',
		// 	'MAIL_PORT' => 587,
		// 	'MAIL_FROM_NAME' => 'Stock Information System'
		// ]
	],
];
