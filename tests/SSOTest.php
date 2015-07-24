<?php

use \P7\SSO;

class SSOTest extends PHPUnit_Framework_TestCase
{
  const CODE = 'Gwl9EvEyG7tyiUF72j3x0TnNvQe6yiQCRlfg4Yb7bLjlbneSoUi2fme4OFpMdblDmSkUgCzOuENbczpX';
  const REDIRECT_URI = 'http://localhost:8000/callback';

  private function validSSO() {
    return new SSO([
      'client_id' => '55b0b8964a616e16b9320000',
      'client_secret' => '6b776407825a50b0f72941315194a3d50886b86b81bc40bbcf1714bdf50b3aa4',
      'environment' => 'test'
    ]);
  }

  public function testGeneratesUri()
  {
    $sso = $this->validSSO();

    $uri = $sso->uri([
      'redirect_uri' => self::REDIRECT_URI,
      'nonce' => 'somerandomstring'
    ]);

    $this->assertEquals('http://sso.7pass.dev/connect/v1.0/authorize?response_type=code&client_id=55b0b8964a616e16b9320000&scope=openid+profile+email&nonce=somerandomstring&redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fcallback', $uri);
  }

  /**
  * @vcr callback_valid_code
  */
  public function testReturnsAllTokensWithValidCode()
  {
    $sso = $this->validSSO();

    $response = $sso->callback([
      'redirect_uri' => self::REDIRECT_URI,
      'code' => self::CODE
    ]);

    $data = $response->data;

    $this->assertObjectHasAttribute('access_token', $data);
    $this->assertObjectHasAttribute('refresh_token', $data);
    $this->assertObjectHasAttribute('id_token', $data);

    return $data;
  }

  /**
  * @depends testReturnsAllTokensWithValidCode
  * @vcr api_get_elevated_access
  */
  public function testGetsAccountInfoWithAccessTokenWithElevatedAccess($tokens) {
    $sso = $this->validSSO();
    $api = $sso->api($tokens->access_token);

    $data = $api->get('/me')->data;
    $this->assertObjectHasAttribute('birthdate', $data);
    $this->assertObjectHasAttribute('email_id', $data);
    $this->assertObjectHasAttribute('email_verified', $data);
  }

  /**
  * @depends testReturnsAllTokensWithValidCode
  * @vcr api_get_standard_access
  */
  public function testGetsAccountInfoWithAccessTokenWithoutClientSecret($tokens) {
    $sso = $this->validSSO();
    $sso->config->client_secret = null;
    $api = $sso->api($tokens->access_token);

    $data = $api->get('/me')->data;
    $this->assertObjectHasAttribute('birthdate', $data);
    $this->assertObjectHasAttribute('email_id', $data);
    $this->assertObjectNotHasAttribute('email_verified', $data);
  }

  /**
  * @vcr callback_invalid_code
  */
  public function testReturnsErrorWithInvalidCode()
  {
    $sso = $this->validSSO();

    $response = $sso->callback([
      'redirect_uri' => 'http://localhost:8000/callback',
      'code' => 'mesoinvalid'
    ]);

    $error = $response->error;

    $this->assertObjectHasAttribute('description', $error);
    $this->assertEquals('Authorization code is invalid', $error->description);
  }
}
