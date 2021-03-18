<?php

namespace products;

class Product {
	public static function getProducts($req, $res, $container) {
		$db = $container->get('database');
		$db->query("SELECT * FROM test_products");
		$products = $db->resultSet();
		return $res->withJson($products);
	}

	public static function selectProduct($req, $res, $container) {
		$id = $req->getAttribute('id');
		$values = array($id);

		$db = $container->get('database');
		$db->query("SELECT * FROM test_products WHERE id=?");
		$db->bind('i', $values);
		$db->execute();

		$product = $db->single();
		if (!is_null($product)) {
			return $res->withJson($product);
		} else {
			return $res->withJson(['msg' => 'product not found'], 404);
		}
	}

	public static function insertProduct($req, $res, $container) {
		$inputs = $req->getParsedBody();
		if (!isset($inputs)) {
			return $res->withJson(["msg" => "insert product fail"], 400);
		}

		$inputValidation = array(
			'name' => FILTER_SANITIZE_SPECIAL_CHARS,
			'name' => FILTER_SANITIZE_STRING,
			'price' => FILTER_SANITIZE_NUMBER_FLOAT,
			'price' => FILTER_VALIDATE_FLOAT,
			'img' => FILTER_SANITIZE_SPECIAL_CHARS,
			'img' => FILTER_SANITIZE_STRING
		);
		$inputs = filter_var_array($inputs, $inputValidation);

		$invalid_input = [];
		foreach ($inputs as $input => $val) {
			if (empty($val)) {
				$invalid_input[] = $input;
			}
		}

		if (!empty($invalid_input)) {
			return $res->withJson([
				'msg' => 'input invalid',
				'invalid_input' => $invalid_input
			], 400);
		} else {
			$values = array_values($inputs);
			$db = $container->get('database');
			$db->query("INSERT INTO test_products
									(name, price, img)
									VALUES (?,?,?)");
			$db->bind('sds', $values);
			$db->execute();

			if ($db->rowCount() > 0) {
				return $res->withJson(["msg" => "insert product success"], 201);
			} else {
				return $res->withJson(["msg" => "insert product fail"], 500);
			}
		}
	}
}
