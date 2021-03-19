<?php

namespace Tests\Functional;

class RegistTest extends BaseTestCase {
  /** TEST REGIST ADMIN */
  /** Test that the regist admmin return success*/
  public function test_regist_admin_success() {
    $regist_data = [
      'username' => 'admin1',
      'email' => 'admin1@mail.co',
      'password' => '123',
      'privilege' => 'admin'
    ];

    $response = $this->runApp('POST', '/regist', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('registration success', $result['msg']);
  }

  /** Test that the regist admin without data return fail*/
  public function test_regist_admin_without_data_fail() {
    $response = $this->runApp('POST', '/regist');
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('registration fail', $result['msg']);
  }

  /** Test that the regist admin with exist username return fail */
  public function test_regist_with_exist_username_fail() {
    $regist_data = [
      'username' => 'admin1',
      'email' => 'admin2@mail.co',
      'password' => '123',
      'privilege' => 'admin'
    ];

    $response = $this->runApp('POST', '/regist', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(409, $response->getStatusCode());
    $this->assertEquals('registration fail. username already registered', $result['msg']);
  }

  /** Test that the regist admin with exist email return fail */
  public function test_regist_with_exist_email_fail() {
    $regist_data = [
      'username' => 'admin2',
      'email' => 'admin1@mail.co',
      'password' => '123',
      'privilege' => 'admin'
    ];

    $response = $this->runApp('POST', '/regist', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(409, $response->getStatusCode());
    $this->assertEquals('registration fail. email already registered', $result['msg']);
  }

  /** Test that the regist admin with invalid email return fail*/
  public function test_regist_with_invalid_email_fail() {
    $regist_data = [
      'username' => 'admin2',
      'email' => 'admin1sdfwef',
      'password' => '123',
      'privilege' => 'admin'
    ];

    $response = $this->runApp('POST', '/regist', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['email']], ['invalid_input' => ['email']]);
  }

  /** Test that the regist admin with invalid privilege return fail */
  public function test_regist_with_invalid_privilege_fail() {
    $regist_data = [
      'username' => 'admin2',
      'email' => 'admin2@mail.co',
      'password' => '123',
      'privilege' => 'superadmin'
    ];

    $response = $this->runApp('POST', '/regist', $regist_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['privilege']], ['invalid_input' => ['privilege']]);
  }
}
