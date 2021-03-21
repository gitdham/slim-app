<?php

namespace Tests\Functional;

class LoginTest extends BaseTestCase {
  /** TEST LOGIN USER */
  /** Test that the regist admmin return success*/
  // public function test_regist_user_success() {
  //   $regist_data = [
  //     'username' => 'user1',
  //     'email' => 'user1@mail.co',
  //     'password' => '123',
  //     'privilege' => 'user'
  //   ];

  //   $response = $this->runApp('POST', '/regist', $regist_data);
  //   $result = json_decode($response->getBody(), true);

  //   $this->assertEquals(201, $response->getStatusCode());
  //   $this->assertEquals('registration success', $result['msg']);
  // }

  /** Test that the login user without data return fail*/
  public function test_login_user_without_data_fail() {
    $response = $this->runApp('POST', '/login');
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
  }

  /** Test that the login user with unregistered username return fail */
  public function test_login_user_with_unregistered_username_fail() {
    $regist_data = [
      'username' => 'userZZ',
      'password' => '123',
    ];

    $response = $this->runApp('POST', '/login', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(404, $response->getStatusCode());
    $this->assertEquals('login fail. username not found/registered', $result['msg']);
  }

  /** Test that the login user with wrong password return fail */
  public function test_login_user_with_wrong_password_fail() {
    $regist_data = [
      'username' => 'user1',
      'password' => 'wrong password',
    ];

    $response = $this->runApp('POST', '/login', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(401, $response->getStatusCode());
    $this->assertEquals('login fail. password not match', $result['msg']);
  }
}
