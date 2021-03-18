<?php

namespace Tests\Functional;

class ProductTest extends BaseTestCase {

  /** GET PTRODUCT ROUTE */
  /**
   * Test that the get route return all products data
   */
  public function test_get_all_products() {
    $response = $this->runApp('GET', '/product');
    $data = json_decode($response->getBody(), true)[0];

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertArrayHasKey('id', $data);
    $this->assertArrayHasKey('name', $data);
    $this->assertArrayHasKey('price', $data);
    $this->assertArrayHasKey('img', $data);
  }

  /**
   * Test that the get route return selected product data
   */
  public function test_get_selected_product() {
    $response = $this->runApp('GET', '/product/1');
    $data = json_decode($response->getBody(), true);
    $values = $data["values"];

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertArrayHasKey('id', $values);
    $this->assertArrayHasKey('name', $values);
    $this->assertArrayHasKey('price', $values);
    $this->assertArrayHasKey('img', $values);

    // check value
    $this->assertEquals(1, $values['id']);
    $this->assertEquals('barang1', $values['name']);
    $this->assertEquals(789.00, $values['price']);
    $this->assertEquals('gambar1.png', $values['img']);
  }

  /**
   * Test that the get route return selected product data not found
   */
  public function test_get_selected_product_not_found() {
    $response = $this->runApp('GET', '/product/barangabc');

    $this->assertEquals(404, $response->getStatusCode());
    $this->assertContains('product not found', json_decode($response->getBody(), true));
  }




  /** POST PRODUCT ROUTE */
  /**
   * Test that the post route return success
   */
  public function test_post_product_success() {
    $post_data = [
      'name' => 'test post product',
      'price' => 9999,
      'img' => 'test_img.png'
    ];

    $response = $this->runApp('POST', '/product', $post_data);

    $this->assertEquals(201, $response->getStatusCode());
    $this->assertContains('insert product success', json_decode($response->getBody(), true));
  }

  /**
   * Test that the post route without data return fail
   */
  public function test_post_product_without_data() {
    $response = $this->runApp('POST', '/product');

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertContains('insert product fail', json_decode($response->getBody(), true));
  }

  /**
   * Test that the post route without img return fail
   */
  public function test_post_product_without_image() {
    $post_data = [
      'name' => 'test post product',
      // 'price' => 9999
      // 'img' => 'test_img.png'
    ];

    $response = $this->runApp('POST', '/product', $post_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['img']], ['invalid_input' => ['img']]);
  }

  /**
   * Test that the post route without price return fail
   */
  public function test_post_product_without_price() {
    $post_data = [
      'name' => 'test post product',
      // 'price' => 9999
      'img' => 'test_img.png'
    ];

    $response = $this->runApp('POST', '/product', $post_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['price']], ['invalid_input' => ['price']]);
  }

  /**
   * Test that the post route with invalid price return fail
   */
  public function test_post_product_with_invalid_price() {
    $post_data = [
      'name' => 'test post product',
      'price' => 'aswqwerty',   //price must decimal number
      'img' => 'test_img.png'
    ];

    $response = $this->runApp('POST', '/product', $post_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['price']], ['invalid_input' => ['price']]);
  }

  /**
   * Test that the post route without name return fail
   */
  public function test_post_product_without_name() {
    $post_data = [
      // 'name' => 'test post product',
      'price' => 9999,
      'img' => 'test_img.png'
    ];

    $response = $this->runApp('POST', '/product', $post_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['name']], ['invalid_input' => ['name']]);
  }




  /** PUT PRODUCT ROUTE */
  /**
   * Test that the put route return success
   */
  // public function test_put_product_success() {
  //   $put_data = [
  //     'id' => 2,
  //     'name' => 'test update product',
  //     'price' => 8888,
  //     'img' => 'updated_test_img.png'
  //   ];

  //   $response = $this->runApp('PUT', '/product', $put_data);

  //   $this->assertEquals(201, $response->getStatusCode());
  //   $this->assertContains('update product success', json_decode($response->getBody(), true));
  // }

  /**
   * Test that the put route without data return fail
   */
  public function test_put_product_without_data() {
    $response = $this->runApp('PUT', '/product');

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertContains('update product fail', json_decode($response->getBody(), true));
  }

  /**
   * Test that the put route without id return fail
   */
  public function test_put_product_without_id() {
    $put_data = [
      // 'id' => 2,
      'name' => 'test update product',
      'price' => 8888,
      'img' => 'updated_test_img.png'
    ];

    $response = $this->runApp('PUT', '/product', $put_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['id']], ['invalid_input' => ['id']]);
  }

  /**
   * Test that the put route with invalid id return fail
   */
  public function test_put_product_with_invalid_id() {
    $put_data = [
      'id' => 'asd',
      'name' => 'test update product',
      'price' => 8888,
      'img' => 'updated_test_img.png'
    ];

    $response = $this->runApp('PUT', '/product', $put_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['id']], ['invalid_input' => ['id']]);
  }

  /**
   * Test that the put route without img return fail
   */
  public function test_put_product_without_image() {
    $put_data = [
      'id' => 2,
      'name' => 'test update product',
      'price' => 8888,
      // 'img' => 'updated_test_img.png'
    ];

    $response = $this->runApp('PUT', '/product', $put_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['img']], ['invalid_input' => ['img']]);
  }

  /**
   * Test that the put route without price return fail
   */
  public function test_put_product_without_price() {
    $put_data = [
      'id' => 2,
      'name' => 'test update product',
      // 'price' => 8888,
      'img' => 'updated_test_img.png'
    ];

    $response = $this->runApp('PUT', '/product', $put_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['price']], ['invalid_input' => ['price']]);
  }

  /**
   * Test that the put route with invalid price return fail
   */
  public function test_put_product_with_invalid_price() {
    $put_data = [
      'id' => 2,
      'name' => 'test update product',
      'price' => 'aswqwerty',   //price must decimal number
      'img' => 'updated_test_img.png'
    ];

    $response = $this->runApp('PUT', '/product', $put_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['price']], ['invalid_input' => ['price']]);
  }

  /**
   * Test that the put route without name return fail
   */
  public function test_put_product_without_name() {
    $put_data = [
      'id' => 2,
      // 'name' => 'test update product',
      'price' => 8888,
      'img' => 'updated_test_img.png'
    ];

    $response = $this->runApp('PUT', '/product', $put_data);
    $result = json_decode($response->getBody(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals('input invalid', $result['msg']);
    $this->assertArraySubset(['invalid_input' => ['name']], ['invalid_input' => ['name']]);
  }
}
