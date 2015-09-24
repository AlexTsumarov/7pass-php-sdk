<?php

use \P7\SSO;
use \P7\SSO\Http;
use \GuzzleHttp\Client;

class SSOTest extends PHPUnit_Framework_TestCase
{
  const CODE = '4s30A27LuWypNLIHlqfuJDu4aeWGzqnPXf4q0AdrL6JA5DHi1ppR1arbx0me3vTfTeWCt5CBB6lN7Msg';
  const REDIRECT_URI = 'http://localhost:8000/callback';
  const SERVER_KID = '4cee9dc4d2aaf2eb997113d6b76dc6fe';
  const SERVER_PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----\r\n"
                            . "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCtsObxWfrFIGbxahs3YM4AvCbd\r\n"
                            . "9gPo8WL6WhHUH+8kgS44TNnzguGK3pPPM87XdF1E3GCyBqhNDt/Y2KogSqeTTnra\r\n"
                            . "pXfAXip7ZN1VyMkibPZ3VtaAuIED66B71UyU8eW+hCgB+pGMFWtsK7X4A08yCyVP\r\n"
                            . "lstPE6F7Cg2zgKIRXwIDAQAB\r\n"
                            . "-----END PUBLIC KEY-----";

  public function __construct() {
    $this->defaultConfig = [
      'client_id' => '54523ed2d3d7a3b4333a9426',
      'client_secret' => 'd7078d0b804522d6c28677d826e39879122c7a80214cc9bfa60be6022f503fec',
      'environment' => 'test',
      //'backoffice_key' => file_get_contents(__DIR__ . '/fixtures/certs/rsa.pem'),
      //'service_id' => '123',
      //'jwks' => ['4cee9dc4d2aaf2eb997113d6b76dc6fe' => self::SERVER_PUBLIC_KEY]
    ];
  }

  private function validSSO() {
    return new SSO(new SSO\Configuration($this->defaultConfig));
  }

  /**
   * @vcr valid_discovery
   */
  public function testGeneratesUri()
  {
    $sso = $this->validSSO();
    $authorization = $sso->authorization();

    $uri = $authorization->uri([
      'redirect_uri' => self::REDIRECT_URI,
      'nonce' => 'somerandomstring'
    ]);

    $this->assertEquals('http://sso.7pass.dev/connect/v1.0/authorize?response_type=code&client_id=54523ed2d3d7a3b4333a9426&scope=openid+profile+email&redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fcallback&nonce=somerandomstring', $uri);
  }

  public function testAuthorizationCache()
  {
    $sso = $this->validSSO();
    $authorization1 = $sso->authorization();
    $authorization2 = $sso->authorization();

    $this->assertSame($authorization1, $authorization2);
  }

  /**
  * @vcr callback_valid_code
  */
  public function testReturnsAllTokensWithValidCode()
  {

    $conf = new SSO\Configuration($this->defaultConfig);

    $authorization = $this->getMockBuilder('P7\SSO\Authorization')
      ->setConstructorArgs([$conf])
      ->setMethods(['decodeIdToken'])
      ->getMock();

    $authorization->method('decodeIdToken')->will($this->returnValue((object)[]));

    $tokens = $authorization->callback([
      'redirect_uri' => self::REDIRECT_URI,
      'code' => self::CODE
    ]);

    $this->assertObjectHasAttribute('access_token', $tokens);
    $this->assertObjectHasAttribute('refresh_token', $tokens);
    $this->assertObjectHasAttribute('id_token', $tokens);
    $this->assertObjectHasAttribute('id_token_decoded', $tokens);

    return $tokens;
  }

  /**
   * @depends testReturnsAllTokensWithValidCode
   * @vcr refresh_valid_code
   */
  public function testReturnsAllTokensWithRefreshToken($tokens) {
    $sso = $this->validSSO();
    $authorization = $sso->authorization();

    $data = $authorization->refresh([
      'refresh_token' => $tokens->refresh_token
    ]);

    $this->assertObjectHasAttribute('access_token', $data);
    $this->assertObjectHasAttribute('refresh_token', $data);
    $this->assertObjectHasAttribute('id_token', $data);
    $this->assertObjectHasAttribute('id_token_decoded', $data);

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
    $sso->getConfig()->client_secret = null;
    $api = $sso->api($tokens->access_token);

    $data = $api->get('/me')->data;
    $this->assertObjectHasAttribute('birthdate', $data);
    $this->assertObjectHasAttribute('email_id', $data);
    $this->assertObjectNotHasAttribute('email_verified', $data);
  }

  /**
   * @vcr callback_invalid_code
   *
   * @expectedException P7\SSO\Exception\BadRequestException
   */
  public function testReturnsErrorWithInvalidCode()
  {
    $sso = $this->validSSO();
    $authorization = $sso->authorization();

    $authorization->callback([
      'redirect_uri' => self::REDIRECT_URI,
      'code' => 'mesoinvalid'
    ]);
  }

  /**
   * @vcr jwks
   */
  public function testDiscoversJwks()
  {
//    $pool = SSO::cache();
//    $item = $pool->getItem('config/jwks/test');
//    $item->clear();
//
//    $sso = new SSO(['environment' => 'test']);
//    $key = $sso->config->jwks[self::SERVER_KID];
//
//    $this->assertEquals(self::SERVER_PUBLIC_KEY, $key);
  }

  /**
   * @vcr jwks
   */
  public function testRediscoverAfterClear()
  {
//    $pool = SSO::cache();
//    $item = $pool->getItem('config/jwks/test');
//
//    $item->set(['foo' => 'bar']);
//
//    $sso = new SSO(['environment' => 'test']);
//    $this->assertEquals('bar', $sso->config->jwks['foo']);
//
//    $item->clear();
//
//    $sso = new SSO(['environment' => 'test']);
//    $this->assertEquals(self::SERVER_PUBLIC_KEY, $sso->config->jwks[self::SERVER_KID]);
  }
}
