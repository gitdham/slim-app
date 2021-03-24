<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use products\Product;
use users\User;
use Firebase\JWT\JWT;

return function (App $app) {
	$container = $app->getContainer();

	$app->get('/about', function (Request $request, Response $response) use ($container) {
		// log message
		$container->get('logger')->info("This is a log info");
		$container->get('logger')->notice("This is a log info");
		$container->get('logger')->warning("This is a log warning");
		$container->get('logger')->error("This is a log error");
		$container->get('logger')->critical("This is a log critical");
		$container->get('logger')->alert("This is a log alert");
		$container->get('logger')->emergency("This is a log emergency");

		return "about page";
	});

	$aboutMiddle = function ($req, $res, $next) {
		echo 'about middleware';
		return $res = $next($req, $res);
	};

	$app->post('/about', function (Request $request, Response $response) {
		return "about from post\n";
	})->add($aboutMiddle);

	// REGISTRATION ROUTE
	$app->post('/user/regist', function (Request $req, Response $res) use ($container) {
		$result = User::registUser($req,  $res, $container);
		return $result;
	});

	// USER ACTIVATION ROUTE
	$app->get('/user/regist/verification', function (Request $req, Response $res) use ($container) {
		return User::userVerification($req, $res, $container);
	});

	// LOGIN ROUTE
	$app->post('/user/login', function (Request $req, Response $res) use ($container) {
		$result = User::loginUser($req,  $res, $container);
		return $result;
	});

	// REFRESH TOKEN ROUTE
	$app->post('/token', function (Request $req, Response $res) use ($container) {
		return User::refreshToken($req, $res, $container);
	});

	// User Auth Middleware
	$user_auth = function ($req, $res, $next) use ($container) {
		$headers = $req->getHeaders();

		if (!isset($headers['HTTP_AUTHORIZATION'])) {
			return $res->withJson(['error' => 'unauthorize'], 401);
		}

		$auth_header = $headers['HTTP_AUTHORIZATION'][0];
		$jwt = explode(' ', $auth_header)[1];
		$secret_key = $container->get('JWT_ACCESS_TOKEN_SECRET_KEY');

		try {
			$jwt_decoded = JWT::decode($jwt, $secret_key, ['HS256']);
			$payload = json_decode(json_encode($jwt_decoded), true);
			return $res = $next($req, $res);
		} catch (UnexpectedValueException $err) {
			return $res->withJson(['error' => $err->getMessage()], 401);
		}
	};


	// API ROUTE
	$app->group('/api', function () use ($app, $container) {
		// PRODUCT ROUTE
		$app->get('/products', function (Request $req, Response $res) use ($container) {
			$result = Product::getProducts($req,  $res, $container);
			return $result;
		});

		$app->get('/product/{id}', function (Request $req, Response $res) use ($container) {
			$result = Product::selectProduct($req,  $res, $container);
			return $result;
		});

		$app->post('/product', function (Request $req, Response $res) use ($container) {
			$result = Product::insertProduct($req,  $res, $container);
			return $result;
		});

		$app->put('/product', function (Request $req, Response $res) use ($container) {
			$result = Product::updateProduct($req,  $res, $container);
			return $result;
		});

		$app->delete('/product/{id}', function (Request $req, Response $res) use ($container) {
			$result = Product::deleteProduct($req, $res, $container);
			return $result;
		});
	})->add($user_auth);
};
