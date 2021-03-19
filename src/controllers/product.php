<?php

namespace products;

class Product {
	// GET PRODUCTS
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
			return $res->withJson([
				'msg' => 'get product success',
				'values' => $product
			]);
		} else {
			return $res->withJson(['msg' => 'product not found'], 404);
		}
	}


	// INSERT PRODUCT
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


	// UPDATE PRODUCT
	public static function updateProduct($req, $res, $container) {
		$inputs = $req->getParsedBody();
		if (!isset($inputs)) {
			return $res->withJson(["msg" => "update product fail"], 400);
		}

		$inputValidation = array(
			'id' => FILTER_SANITIZE_NUMBER_INT,
			'id' => FILTER_VALIDATE_INT,
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
		}

		// check selected product
		$id = $inputs['id'];
		$product = self::selectProduct($req->withAttribute('id', $id), $res, $container);
		$product = json_decode($product->getBody(), true);

		if ($product['msg'] === 'product not found') {
			return $res->withJson($product, 404);
		}

		// update product
		$values = array_values($inputs);
		// put id to last array
		$values = array_merge(array_splice($values, 1), $values);

		$db = $container->get('database');
		$db->query("UPDATE test_products
								SET name=?, price=?, img=?
								WHERE id=?");
		$db->bind('sdsi', $values);
		$db->execute();

		if ($db->rowCount() > 0) {
			return $res->withJson(["msg" => "update product success"], 201);
		} else {
			return $res->withJson(["msg" => "update product fail"], 500);
		}
	}

	// DELETE PRODUCT
	public static function deleteProduct($req, $res, $container) {
		$id = $req->getAttribute('id');

		$product = self::selectProduct($req->withAttribute('id', $id), $res, $container);
		$product = json_decode($product->getBody(), true);

		if ($product['msg'] === 'product not found') {
			return $res->withJson($product, 404);
		}


		$values = array($id);

		$db = $container->get('database');
		$db->query("DELETE FROM test_products WHERE id=?");
		$db->bind('i', $values);
		$db->execute();

		if ($db->rowCount() > 0) {
			return $res->withJson(["msg" => "delete product success"]);
		} else {
			return $res->withJson(["msg" => "delete product fail"], 500);
		}
	}
}
