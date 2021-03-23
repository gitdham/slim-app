<?php

// require __DIR__ . './database.php';

use Slim\App;
use Slim\Http\Response;

return function (App $app) {
	$container = $app->getContainer();

	// view renderer
	$container['renderer'] = function ($c) {
		$settings = $c->get('settings')['renderer'];
		return new \Slim\Views\PhpRenderer($settings['template_path']);
	};

	// monolog
	$container['logger'] = function ($c) {
		$settings = $c->get('settings')['logger'];
		$logger = new \Monolog\Logger($settings['name']);
		$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
		$logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
		return $logger;
	};

	// database
	$container['database'] = function ($c) {
		$settings = $c->get('settings')['database'];
		$db = new Database($settings['host'], $settings['username'], $settings['password'], $settings['database'], $settings['port']);
		return $db;
	};

	// jwt key
	$container['JWT_ACCESS_TOKEN_SECRET_KEY'] = function ($c) {
		$settings = $c->get('settings')['JWT_SECRET'];
		$access_token_secret = $settings['ACCESS_TOKEN_SECRET'];
		return $access_token_secret;
	};
	$container['JWT_REFRESH_TOKEN_SECRET_KEY'] = function ($c) {
		$settings = $c->get('settings')['JWT_SECRET'];
		$refresh_token_secret = $settings['REFRESH_TOKEN_SECRET'];
		return $refresh_token_secret;
	};
};
