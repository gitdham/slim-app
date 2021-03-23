<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use products\Product;
use users\User;
use Firebase\JWT\JWT;

return function (App $app) {
	$container = $app->getContainer();

	// $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
	// 	// Sample log message
	// 	$container->get('logger')->info("Slim-Skeleton '/' route");

	// 	// Render index view
	// 	return $container->get('renderer')->render($response, 'index.phtml', $args);
	// });

	$app->get('/about', function (Request $request, Response $response) use ($container) {
		// log message
		$container->get('logger')->info("Slim-Skeleton '/about' route");

		return "about page";
	});

	$app->post('/about', function (Request $request, Response $response) {
		return 'about from post';
	});

	// REGISTRATION ROUTE
	$app->post('/user/regist', function (Request $req, Response $res) use ($container) {
		$result = User::registUser($req,  $res, $container);
		return $result;
	});


	// LOGIN ROUTE
	$app->post('/user/login', function (Request $req, Response $res) use ($container) {
		$result = User::loginUser($req,  $res, $container);
		return $result;
	});

	// REFRESH TOKEN ROUTE
	$app->post('/token', function (Request $req, Response $res) use ($container) {
		$refresh_tokens = ['eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6InVzZXIxIiwicHJpdmlsZWdlIjoidXNlciJ9.g7pfm4sigeUXTGL6Bk1JmmuIXAVZi-nUOONkLxD-olg'];

		if (is_null($req->getParsedBody())) {
			return $res->withStatus(401);
		}

		// if (!in_array('refresh_token',  $req->getParsedBody())) {
		// 	return $res->withStatus(403);
		// }

		$refresh_token = $req->getParsedBody()['refresh_token'];

		if (!in_array($refresh_token,  $refresh_tokens)) {
			echo 'not match';
		}

		$secret_refresh_key = $container->get('JWT_REFRESH_TOKEN_SECRET_KEY');

		try {
			$jwt_decoded = JWT::decode($refresh_token, $secret_refresh_key, ['HS256']);
			$user = json_decode(json_encode($jwt_decoded), true);
			$access_token = User::generateAccessToken($user, $container);

			return $res->withJson(['access_token' => $access_token]);
		} catch (UnexpectedValueException $err) {
			return $res->withJson(['error' => $err->getMessage()], 401);
		}
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
		// CUSTOMER ROUTE
		$app->get('/customers', function (Request $request, Response $response) use ($container) {
			$db = $container->get('database');
			$db->query("SELECT * FROM customers");
			$customers = $db->resultSet();
			return $response->withJson($customers);
		});

		$app->get('/customer/{id}', function (Request $request, Response $response) use ($container) {
			$id = $request->getAttribute('id');
			$values = array($id);

			$db = $container->get('database');
			$db->query("SELECT * FROM customers WHERE id=?");
			$db->bind('i', $values);
			$db->execute();

			$customer = $db->single();
			if (!is_null($customer)) {
				return $response->withJson($customer);
			} else {
				return $response->withJson(['msg' => 'customer not found'], 404);
			}
		});

		$app->post('/customer', function (Request $request, Response $response) use ($container) {
			$inputs = $request->getParsedBody();
			if (!isset($inputs)) {
				return $response->withJson(["msg" => "Insert customer fail"], 400);
			}

			$inputValidation = array(
				'first_name' => FILTER_SANITIZE_SPECIAL_CHARS,
				'first_name' => FILTER_SANITIZE_STRING,
				'last_name' => FILTER_SANITIZE_SPECIAL_CHARS,
				'last_name' => FILTER_SANITIZE_STRING,
				'email' => FILTER_SANITIZE_SPECIAL_CHARS,
				'email' => FILTER_VALIDATE_EMAIL,
				'phone' => FILTER_SANITIZE_NUMBER_INT,
				'address' => FILTER_SANITIZE_SPECIAL_CHARS,
				'address' => FILTER_SANITIZE_STRING
			);
			$inputs = filter_var_array($inputs, $inputValidation);

			$invalid_input = [];
			foreach ($inputs as $input => $val) {
				if (empty($val)) {
					$invalid_input[] = $input;
				}
			}

			if (!empty($invalid_input)) {
				return $response->withJson([
					'msg' => 'input invalid',
					'inputs' => $invalid_input
				], 400);
			} else {
				$values = array_values($inputs);
				$db = $container->get('database');
				$db->query("INSERT INTO customers VALUES ('',?,?,?,?,?)");
				$db->bind('sssss', $values);
				$db->execute();

				if ($db->rowCount() > 0) {
					return $response->withJson(["msg" => "Insert customer success"]);
				} else {
					return $response->withJson(["msg" => "Insert customer fail"], 500);
				}
			}
		});


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
