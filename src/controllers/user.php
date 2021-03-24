<?php

namespace users;

use \Firebase\JWT\JWT;
use \mail\Mail;

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
    $inputs['full_name'] = ucwords($inputs['full_name']);
    $inputs['password'] = password_hash($inputs['password'], PASSWORD_DEFAULT);
    $inputs['hash'] = hash('sha256', rand());


    $inputValidation = [
      'username' => FILTER_SANITIZE_SPECIAL_CHARS,
      'username' => FILTER_SANITIZE_STRING,
      'full_name' => FILTER_SANITIZE_SPECIAL_CHARS,
      'full_name' => FILTER_SANITIZE_STRING,
      'email' => FILTER_SANITIZE_EMAIL,
      'email' => FILTER_VALIDATE_EMAIL,
      'password' => FILTER_DEFAULT,
      'hash' => FILTER_DEFAULT,
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
    $db->query("SELECT username FROM users WHERE username=?");
    $db->bind('s', $username);
    $db->execute();

    $user = $db->single();
    if (!is_null($user)) {
      return $res->withJson(['msg' => 'registration fail. username already registered'], 409);
    }

    // check existed email
    $email = array($inputs['email']);
    $db->query("SELECT email FROM users WHERE email=?");
    $db->bind('s', $email);
    $db->execute();

    $email = $db->single();
    if (!is_null($email)) {
      return $res->withJson(['msg' => 'registration fail. email already registered'], 409);
    }

    // run query
    $values = array_values($inputs);
    $db->query("INSERT INTO users
                (username, full_name, email, password, hash)
                VALUES (?,?,?,?,?)");
    $db->bind('sssss', $values);
    $db->execute();

    if ($db->rowCount() <= 0) {
      return $res->withJson(["msg" => "registration fail"], 500);
    }

    // Sent Mail
    $sentMail = self::sendRegisterEmail($inputs['email'], $container);

    if ($sentMail !== 'mail sent') {
      return $res->withJson(["msg" => "failed to send email"], 500);
    }

    return $res->withJson(["msg" => "registration success. check your email for verification"], 201);
  }

  public static function sendRegisterEmail($registeredEmail, $container) {
    // db init
    $db = $container->get('database');

    // get registered data by email
    $email = array($registeredEmail);
    $db->query("SELECT username, full_name, email, hash FROM users WHERE email=?");
    $db->bind('s', $email);
    $db->execute();

    $user = $db->single();
    $username = $user['username'];
    $full_name = $user['full_name'];
    $email = $user['email'];
    $hash = $user['hash'];

    // Set Message
    $subject = 'SI Stok Barang';
    $body = '
      <p>Hi ' . $full_name . ',</p>
      <p>Terima kasih sudah registrasi sebagai user dengan username ' . $username . '. Silahkan click tombol di bawah ini untuk menyelesaikan registrasi akun anda.</p>
      <a href="localhost:8080/user/regist/verification?email=' . $email . '&hash=' . $hash . '" style="-webkit-border-radius: 28;
      -moz-border-radius: 28;
      border-radius: 28px;
      font-family: Arial;
      color: #ffffff;
      font-size: 20px;
      background: #3498db;
      padding: 10px 20px 10px 20px;
      text-decoration: none;">Verify Now</a>
      <br><br>
      <p>Click/copy link berikut apabila tombol diatas tidak berfungsi</p>
      <a href="localhost:8080/user/regist/verification?email=' . $email . '&hash=' . $hash . '">localhost:8080/user/regist/verification?email=' . $email . '&hash=' . $hash . '</a>
    ';

    $alt_body = "
    Hi {$full_name}, Terima kasih sudah registrasi sebagai user dengan username {$username}. Silahkan click link berikut untuk menyelesaikan registrasi akun anda. localhost:8080/user/regist/verification?email={$email}&hash={$hash}
    ";

    // sent mail
    $mail = new Mail($email, $full_name, $subject, $body, $alt_body);
    return $mail->sentMail();
  }

  // USER ACTIVATION
  public static function userVerification($req, $res, $container) {
    // db init
    $db = $container->get('database');

    $email = $req->getQueryParam('email');
    $hash = $req->getQueryParam('hash');

    // get data from db where emai and hash match
    $values = array($email, $hash);
    $db->query("SELECT email, hash, active_status FROM users WHERE email=? AND hash=?");
    $db->bind('ss', $values);
    $db->execute();

    $user = $db->single();
    // if user not found
    if (is_null($user)) {
      return $res->withStatus(401);
    }

    // if user active
    if ($user['active_status'] === '1') {
      return $res->withJson(["msg" => "user already active"], 403);
    }

    // run query
    $db->query("UPDATE users SET active_status = '1' WHERE email=? AND hash=?");
    $db->bind('ss', $values);
    $db->execute();

    if ($db->rowCount() <= 0) {
      return $res->withJson(["msg" => "activation error"], 500);
    }

    return $res->withJson(["msg" => "activation success"], 201);
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
    $db->query("SELECT username, password, privilege FROM users WHERE username=?");
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
    $secret_refresh_key = $container->get('JWT_REFRESH_TOKEN_SECRET_KEY');

    $access_token = self::generateAccessToken($user, $container);
    $refresh_token = JWT::encode($user, $secret_refresh_key);

    return $res->withJson([
      'msg' => 'login success',
      'access_token' => $access_token,
      'refresh_token' => $refresh_token
    ]);
  }

  // REFRESH ACCESS TOKEN
  public static function refreshToken($req, $res, $container) {
    $refresh_tokens = ['eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6InVzZXIxIiwicHJpdmlsZWdlIjoidXNlciJ9.g7pfm4sigeUXTGL6Bk1JmmuIXAVZi-nUOONkLxD-olg'];

    if (is_null($req->getParsedBody())) {
      return $res->withStatus(401);
    }

    $refresh_token = $req->getParsedBody()['refresh_token'];

    if (!in_array($refresh_token,  $refresh_tokens)) {
      return $res->withJson(['error' => 'refresh token invalid'], 401);
    }

    $secret_refresh_key = $container->get('JWT_REFRESH_TOKEN_SECRET_KEY');

    $jwt_decoded = JWT::decode($refresh_token, $secret_refresh_key, ['HS256']);
    $user = json_decode(json_encode($jwt_decoded), true);
    $access_token = self::generateAccessToken($user, $container);

    return $res->withJson(['access_token' => $access_token]);
  }

  // GENERATE ACCESS TOKEN
  private static function generateAccessToken($user, $container) {
    $secret_access_key = $container->get('JWT_ACCESS_TOKEN_SECRET_KEY');
    $iat = time();
    $exp = $iat + 60 * 60;
    $payload = [
      // 'iss' => 'http://localhost:8080',
      // 'aud' => 'http://127.0.0.1:5500',
      'iat' => $iat,
      'exp' => $exp,
      'data' => $user
    ];

    return JWT::encode($payload, $secret_access_key);
  }
}
