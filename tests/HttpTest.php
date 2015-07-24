<?php

use \P7\SSO\Http;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \GuzzleHttp\Handler\MockHandler;
use \GuzzleHttp\Psr7\Response;

class HttpTest extends PHPUnit_Framework_TestCase
{
  /**
  * @vcr http_data_merge_get
  */
  public function testDataMergeGet() {
    $client = new Http(['data' => ['foo' => 'bar']]);
    $response = $client->get('http://httpbin.org/get', ['bar' => 'baz']);
    $args = $response->data->args;

    $this->assertEquals('bar', $args->foo);
    $this->assertEquals('baz', $args->bar);
  }

  /**
  * @vcr http_data_merge_post
  */
  public function testDataMergePostJSON() {
    $client = new Http(['data' => ['foo' => 'bar']]);
    $response = $client->post('http://httpbin.org/post', ['bar' => 'baz']);
    $data = $response->data;

    $this->assertEquals('{"foo":"bar","bar":"baz"}', $data);
  }

  /**
  * @vcr http_data_put
  */
  public function testPut() {
    $client = new Http();
    $response = $client->put('http://httpbin.org/put', ['bar' => 'baz']);
    $data = $response->data;

    $this->assertEquals('{"bar":"baz"}', $data);
  }

  /**
  * @vcr http_data_patch
  */
  public function testPatch() {
    $client = new Http();
    $response = $client->patch('http://httpbin.org/patch', ['foo' => 'bar']);
    $decoded = json_decode($response->raw);

    $this->assertEquals('bar', $decoded->args->foo);
  }

  /**
  * @vcr http_data_delete
  */
  public function testDelete() {
    $client = new Http();
    $response = $client->delete('http://httpbin.org/delete', ['foo' => 'bar']);
    $decoded = json_decode($response->raw);

    $this->assertEquals('bar', $decoded->args->foo);
  }

  /**
  * @vcr http_base_uri_get
  */
  public function testWorksWithBaseUri() {
    $client = new Http(['base_uri' => 'http://httpbin.org/']);
    $response = $client->get('/get', ['foo' => 'bar']);
    $data = $response->data;

    $this->assertEquals('http://httpbin.org/get?foo=bar', $data->url);
  }

  /**
  * @vcr http_get_auth_basic
  */
  public function testSendsAuthBasic() {
    $client = new Http([
      'auth' => ['pretty_nick', 'verysecretkey']
    ]);
    $response = $client->get('http://httpbin.org/get');
    $headers = $response->data->headers;

    $this->assertEquals('Basic cHJldHR5X25pY2s6dmVyeXNlY3JldGtleQ==', $headers->Authorization);
  }

  /**
  * @vcr http_get_auth_bearer
  */
  public function testSendsAuthBearer() {
    $client = new Http([
      'headers' => [
        'Authorization' => 'Bearer FOOBAR'
      ]
    ]);
    $response = $client->get('http://httpbin.org/get');
    $headers = $response->data->headers;

    $this->assertEquals('Bearer FOOBAR', $headers->Authorization);
  }
}
