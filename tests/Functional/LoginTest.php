<?php

namespace Tests\Functional;

class LoginTest extends BaseTestCase {
  /** TEST LOGIN USER */
  /** Test that the login user return success*/
  public function test_login_user_success() {
    $login_data = [
      'username' => 'user1',
      'password' => '123',
    ];

    $response = $this->runApp('POST', '/user/login', $login_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('registration success', $result['msg']);
    $this->assertArrayHasKey('access_token', $result);
    $this->assertArrayHasKey('refresh_token', $result);
  }

  /** Test that the login user without data return fail*/
  public function test_login_user_without_data_fail() {
    $response = $this->runApp('POST', '/user/login');
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

    $response = $this->runApp('POST', '/user/login', $regist_data);
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

    $response = $this->runApp('POST', '/user/login', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(401, $response->getStatusCode());
    $this->assertEquals('login fail. password not match', $result['msg']);
  }
}
