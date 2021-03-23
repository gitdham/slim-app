<?php

namespace users;

use \Firebase\JWT\JWT;

class User {
  // GET USERS

  // REGIST USER
  public static function registUser($req, $res, $container) {
    // db init
    $db = $container->get('database');

    $inputs = $req->getParsedBody();
    if (!isset($inputs)) {
      return $res->withJson(["msg" => "input invalid"], 400);
    }

    // set username to lowercase & hashing
    $inputs['username'] = strtolower($inputs['username']);
    $inputs['password'] = password_hash($inputs['password'], PASSWORD_DEFAULT);
    $inputs['reg_hash'] = hash('sha256', rand());


    $inputValidation = [
      'username' => FILTER_SANITIZE_SPECIAL_CHARS,
      'username' => FILTER_SANITIZE_STRING,
      'email' => FILTER_SANITIZE_EMAIL,
      'email' => FILTER_VALIDATE_EMAIL,
      'password' => FILTER_DEFAULT,
      'reg_hash' => FILTER_DEFAULT,
    ];
    $inputs = filter_var_array($inputs, $inputValidation);

    // if there was invalid input
    $invalid_input = [];
    foreach ($inputs as $input => $val) {
      if (empty($val)) {
        $invalid_input[] = $input;
      }
    }

    // response if there was invalid input
    if (!empty($invalid_input)) {
      return $res->withJson([
        'msg' => 'input invalid',
        'invalid_input' => $invalid_input
      ], 400);
    }


    // check existed username
    $username = array($inputs['username']);
    $db->query("SELECT username FROM test_users WHERE username=?");
    $db->bind('s', $username);
    $db->execute();

    $user = $db->single();
    if (!is_null($user)) {
      return $res->withJson(['msg' => 'registration fail. username already registered'], 409);
    }

    // check existed email
    $email = array($inputs['email']);
    $db->query("SELECT email FROM test_users WHERE email=?");
    $db->bind('s', $email);
    $db->execute();

    $email = $db->single();
    if (!is_null($email)) {
      return $res->withJson(['msg' => 'registration fail. email already registered'], 409);
    }

    // run query
    $values = array_values($inputs);
    $db->query("INSERT INTO test_users
                (username, email, password, reg_hash)
                VALUES (?,?,?,?)");
    $db->bind('ssss', $values);
    $db->execute();

    if ($db->rowCount() > 0) {
      return $res->withJson(["msg" => "registration success"], 201);
    } else {
      return $res->withJson(["msg" => "registration fail"], 500);
    }
  }

  // LOGIN USER
  public static function loginUser($req, $res, $container) {
    // db init
    $db = $container->get('database');

    $inputs = $req->getParsedBody();
    if (!isset($inputs)) {
      return $res->withJson(["msg" => "input invalid"], 400);
    }

    $inputValidation = [
      'username' => FILTER_SANITIZE_SPECIAL_CHARS,
      'username' => FILTER_SANITIZE_STRING,
      'password' => FILTER_DEFAULT,
    ];
    $inputs = filter_var_array($inputs, $inputValidation);

    // if there was invalid input
    $invalid_input = [];
    foreach ($inputs as $input => $val) {
      if (empty($val)) {
        $invalid_input[] = $input;
      }
    }

    // response if there was invalid input
    if (!empty($invalid_input)) {
      return $res->withJson([
        'msg' => 'input invalid',
        'invalid_input' => $invalid_input
      ], 400);
    }

    // check existed username
    $username = array($inputs['username']);
    $db->query("SELECT username, password, privilege FROM test_users WHERE username=?");
    $db->bind('s', $username);
    $db->execute();

    $user = $db->single();
    // if username not found
    if (is_null($user)) {
      return $res->withJson(['msg' => 'login fail. username not found/registered'], 404);
    }

    // if password not match
    if (!password_verify($inputs['password'], $user['password'])) {
      return $res->withJson(['msg' => 'login fail. password not match'], 401);
    }


    // unset password from $user
    unset($user['password']);

    $key = 'privatekey';
    $iat = time();
    $exp = $iat + 60 * 60;
    $payload = [
      'iss' => 'http://localhost:8080',
      'aud' => 'http://localhost:8080',
      'iat' => $iat,
      'exp' => $exp,
      'data' => $user
    ];

    $jwt = JWT::encode($payload, $key);
    // $jwt_decoded = JWT::decode($jwt, $key, ['HS256']);

    return $res->withJson([
      'msg' => 'login success',
      'jwt' => $jwt,
      'expires' => $exp
    ]);
  }
}
