<?php

namespace Tests\Functional;

class RegistTest extends BaseTestCase {
  /** TEST REGIST USER */
  /** Test that the regist admmin return success*/
  public function test_regist_user_success() {
    $regist_data = [
      'username' => 'user1',
      'email' => 'user1@mail.co',
      'password' => '123',
      'privilege' => 'user'
    ];

    $response = $this->runApp('POST', '/regist', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(201, $response->getStatusCode());
    $this->assertEquals('registration success', $result['msg']);
  }

  /** Test that the regist user without data return fail*/
  public function test_regist_user_without_data_fail() {
    $response = $this->runApp('POST', '/regist');
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
  }

  /** Test that the regist user with exist username return fail */
  public function test_regist_user_with_exist_username_fail() {
    $regist_data = [
      'username' => 'user1',
      'email' => 'user2@mail.co',
      'password' => '123',
      'privilege' => 'user'
    ];

    $response = $this->runApp('POST', '/regist', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(409, $response->getStatusCode());
    $this->assertEquals('registration fail. username already registered', $result['msg']);
  }

  /** Test that the regist user with exist email return fail */
  public function test_regist_user_with_exist_email_fail() {
    $regist_data = [
      'username' => 'user2',
      'email' => 'user1@mail.co',
      'password' => '123',
      'privilege' => 'user'
    ];

    $response = $this->runApp('POST', '/regist', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(409, $response->getStatusCode());
    $this->assertEquals('registration fail. email already registered', $result['msg']);
  }

  /** Test that the regist user with invalid email return fail*/
  public function test_regist_user_with_invalid_email_fail() {
    $regist_data = [
      'username' => 'user2',
      'email' => 'user1sdfwef',
      'password' => '123',
      'privilege' => 'user'
    ];

    $response = $this->runApp('POST', '/regist', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['email']], ['invalid_input' => ['email']]);
  }
}
