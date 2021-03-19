<?php

namespace users;

class User {
  // GET USERS

  // REGIST USER
  public static function registUser($req, $res, $container) {
    // db init
    $db = $container->get('database');

    $inputs = $req->getParsedBody();
    if (!isset($inputs)) {
      return $res->withJson(["msg" => "registration fail"], 400);
    }

    $inputValidation = array(
      'username' => FILTER_SANITIZE_SPECIAL_CHARS,
      'username' => FILTER_SANITIZE_STRING,
      'email' => FILTER_SANITIZE_EMAIL,
      'email' => FILTER_VALIDATE_EMAIL,
      'password' => FILTER_DEFAULT,
      'privilege' => FILTER_SANITIZE_SPECIAL_CHARS,
      'privilege' => FILTER_SANITIZE_STRING
    );
    $inputs = filter_var_array($inputs, $inputValidation);

    // if there was invalid input
    $invalid_input = [];
    foreach ($inputs as $input => $val) {
      if (empty($val)) {
        $invalid_input[] = $input;
      }
    }

    // check if privilege invalid
    $privileges = ['admin', 'user'];
    if (!in_array($inputs['privilege'], $privileges)) {
      $invalid_input[] = 'privilege';
    }

    // response if there was invalid input
    if (!empty($invalid_input)) {
      return $res->withJson([
        'msg' => 'input invalid',
        'invalid_input' => $invalid_input
      ], 400);
    }

    // check existed username
    $values = array($inputs['username']);
    $db->query("SELECT username FROM test_users WHERE username=?");
    $db->bind('s', $values);
    $db->execute();

    $user = $db->single();
    if (!is_null($user)) {
      return $res->withJson(['msg' => 'registration fail. username already registered'], 409);
    }

    // check existed email
    $values = array($inputs['email']);
    $db->query("SELECT email FROM test_users WHERE email=?");
    $db->bind('s', $values);
    $db->execute();

    $email = $db->single();
    if (!is_null($email)) {
      return $res->withJson(['msg' => 'registration fail. email already registered'], 409);
    }

    // run query
    $values = array_values($inputs);
    $db->query("INSERT INTO test_users
                (username, email, password, privilege)
                VALUES (?,?,?,?)");
    $db->bind('ssss', $values);
    $db->execute();

    if ($db->rowCount() > 0) {
      return $res->withJson(["msg" => "registration success"], 201);
    } else {
      return $res->withJson(["msg" => "registration fail"], 500);
    }
  }
}
