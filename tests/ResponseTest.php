<?php

use \P7\SSO\Response;
use \GuzzleHttp\Client;
use \GuzzleHttp\Handler\MockHandler;
use \GuzzleHttp\Psr7\Response as Psr7Response;
use \GuzzleHttp\HandlerStack;


class ResponseTest extends PHPUnit_Framework_TestCase
{
  public function testSuccessfullOAuthResponse()
  {
    $body ='{"access_token":"...","token_type":"Bearer","refresh_token":"...","expires_in":7200,"id_token":"..."}';
    $response = new Response(200, $body);

    $this->assertEquals($body, $response->raw);
    $this->assertEquals(json_decode($body), $response->data);
    $this->assertEquals(null, $response->error);
    $this->assertEquals(true, $response->success);
    $this->assertEquals(200, $response->code);
  }

  public function testErrorOAuthResponse()
  {
    $body = '{"error":"invalid_grant","error_description":"Authorization code is invalid"}';
    $response = new Response(400, $body);

    $this->assertEquals($body, $response->raw);
    $this->assertEquals(null, $response->data);
    $this->assertEquals('invalid_grant', $response->error->message);
    $this->assertEquals('Authorization code is invalid', $response->error->description);
    $this->assertEquals(false, $response->success);
    $this->assertEquals(400, $response->code);
  }

  public function testSuccessfullApiResponse()
  {
    $body = '{"status":"success","data":{"unsubscribed":true}}';
    $response = new Response(200, $body);

    $this->assertEquals($body, $response->raw);
    $this->assertEquals((object)array('unsubscribed' => true), $response->data);
    $this->assertEquals(null, $response->error);
    $this->assertEquals(true, $response->success);
    $this->assertEquals(200, $response->code);
  }

  public function testErrorApiResponse()
  {
    $body = '{"status":"error","error":{"message":"Missing Parameters","status":400,"detail":"Mandatory parameters are missing."}}';
    $response = new Response(400, $body);

    $this->assertEquals($body, $response->raw);
    $this->assertEquals(null, $response->data);
    $this->assertEquals('Missing Parameters', $response->error->message);
    $this->assertEquals('Mandatory parameters are missing.', $response->error->description);
    $this->assertEquals(false, $response->success);
    $this->assertEquals(400, $response->code);
  }

  public function testSuccessFromGuzzle() {
    $mock = new MockHandler([
      new Psr7Response(200, [], '{"status":"success","data":{"available":true}}'),
    ]);
    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);
    $r = $client->get('/');

    $response = Response::fromGuzzlehttpResponse($r);
    $this->assertEquals(true, $response->data->available);
    $this->assertEquals(null, $response->error);
    $this->assertEquals(true, $response->success);
    $this->assertEquals(200, $response->code);
  }

  public function testErrorFromGuzzle() {
    $mock = new MockHandler([
      new Psr7Response(400, [], '{"status":"error","error":{"message":"invalid_request","status":400,"detail":"username is a mandatory parameter for this action"}}'),
    ]);
    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler, 'http_errors' => false]);
    $r = $client->get('/');

    $response = Response::fromGuzzlehttpResponse($r);
    $this->assertEquals('invalid_request', $response->error->message);
    $this->assertEquals('username is a mandatory parameter for this action', $response->error->description);
    $this->assertEquals(null, $response->data);
    $this->assertEquals(false, $response->success);
    $this->assertEquals(400, $response->code);
  }
}
