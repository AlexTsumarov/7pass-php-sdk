<?php

use \P7\SSO\Response;
use \GuzzleHttp\Client;

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

  /**
   * @vcr valid_response
   */
  public function testSuccessFromGuzzle() {
    $client = new Client();
    $r = $client->post('http://sso.7pass.dev/api/session/checkUsername', array(
      'form_params' => array(
        'username' => 'fancy_nick',
        'flags' => array(
          'client_id' => '55b0b8964a616e16b9320000'
        )
      )
    ));

    $response = Response::fromGuzzlehttpResponse($r);
    $this->assertEquals(true, $response->data->available);
    $this->assertEquals(null, $response->error);
    $this->assertEquals(true, $response->success);
    $this->assertEquals(200, $response->code);
  }

  /**
   * @vcr invalid_response
   */
  public function testErrorFromGuzzle() {
    $client = new Client();
    $r = $client->post('http://sso.7pass.dev/api/session/checkUsername', array(
      'form_params' => array(
        'flags' => array(
          'client_id' => '55b0b8964a616e16b9320000'
        )
      ),
      'http_errors' => false
    ));

    $response = Response::fromGuzzlehttpResponse($r);
    $this->assertEquals('invalid_request', $response->error->message);
    $this->assertEquals('username is a mandatory parameter for this action', $response->error->description);
    $this->assertEquals(null, $response->data);
    $this->assertEquals(false, $response->success);
    $this->assertEquals(400, $response->code);
  }
}
