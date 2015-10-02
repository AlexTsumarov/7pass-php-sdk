<?php

use P7\SSO\ApiClient;

class ApiClientTest extends PHPUnit_Framework_TestCase
{

  protected function createClient($params = []) {
    $client = new ApiClient(array_merge([
      'host' => 'http://httpbin.org'
    ], $params), function($body) {
      return $body;
    });

    return $client;
  }

  /**
  * @vcr http_data_merge_get
  */
  public function testDataMergeGet() {
    $client = $this->createClient(['data' => ['foo' => 'bar']]);
    $response = $client->get('get', ['bar' => 'baz']);

    $args = $response->args;

    $this->assertEquals('bar', $args->foo);
    $this->assertEquals('baz', $args->bar);
  }

  /**
  * @vcr http_data_merge_post
  */
  public function testDataMergePostJSON() {
    $client = $this->createClient(['data' => ['foo' => 'bar']]);
    $data = $client->post('post', ['bar' => 'baz']);

    $this->assertEquals('{"foo":"bar","bar":"baz"}', $data->data);
  }

  /**
  * @vcr http_data_put
  */
  public function testPut() {
    $client = $this->createClient();
    $data = $client->put('put', ['bar' => 'baz']);

    $this->assertEquals('{"bar":"baz"}', $data->data);
  }

  /**
  * @vcr http_data_patch
  */
  public function testPatch() {
    $client = $this->createClient();
    $data = $client->patch('patch', ['foo' => 'bar']);

    $this->assertEquals('bar', $data->json->foo);
  }

  /**
  * @vcr http_data_delete
  */
  public function testDelete() {
    $client = $this->createClient();
    $data = $client->delete('delete', ['foo' => 'bar']);

    $this->assertEquals('bar', $data->args->foo);
  }

  /**
  * @vcr http_base_uri_get
  */
  public function testWorksWithBaseUri() {
    $client = $this->createClient();
    $data = $client->get('get', ['foo' => 'bar']);

    $this->assertEquals('http://httpbin.org/get?foo=bar', $data->url);
  }

}
