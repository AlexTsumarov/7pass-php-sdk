<?php

use Firebase\JWT\JWT;
use \P7\SSO\Authorization;
use \P7\SSO\Configuration;

class AuthorizationTest extends PHPUnit_Framework_TestCase
{

  protected $configDefault = [
    'environment' => 'test',
    'client_id' => '54523ed2d3d7a3b4333a9426',
    'client_secret' => 'd7078d0b804522d6c28677d826e39879122c7a80214cc9bfa60be6022f503fec'
  ];

  protected function getValidAuthorization($configuration = null) {
    if($configuration === null) {
      $configuration = new Configuration($this->configDefault);
    }

    return new Authorization($configuration);
  }

  protected function getValidAuthorizationMock(array $methods, $configuration = null) {

    if($configuration === null) {
      $configuration = new Configuration($this->configDefault);
    }

    return $this->getMockBuilder('P7\SSO\Authorization')
      ->setMethods($methods)
      ->setConstructorArgs([$configuration])
      ->getMock();
  }

  /**
   * @vcr configuration_openid
   */
  public function testGetConfig()
  {
    $authorization = $this->getValidAuthorization();
    $this->assertEquals(new Configuration($this->configDefault), $authorization->getConfig());
  }

  /**
   * @vcr configuration_openid
   */
  public function testAuthorizeUri()
  {
    $authorization = $this->getValidAuthorization();

    $this->assertEquals('http://sso.7pass.dev/connect/v1.0/authorize?response_type=code&client_id=54523ed2d3d7a3b4333a9426&scope=openid+profile+email&redirect_uri=REDIRECT&nonce=foobar',
      $authorization->authorizeUri([
        'redirect_uri' => 'REDIRECT',
        'nonce' => 'foobar'
      ]));
  }

  /**
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testLogoutUriRequiredParamsException()
  {
    $authorization = $this->getValidAuthorization();
    $authorization->logoutUri([]);
  }

  /**
   * @vcr configuration_openid
   */
  public function testLogoutUri()
  {
    $authorization = $this->getValidAuthorization();
    $url = $authorization->logoutUri([
      'id_token_hint' => 'ID_TOKEN',
      'post_logout_redirect_uri' => 'REDIRECT_URL'
    ]);

    $this->assertContains('id_token_hint=ID_TOKEN', $url);
    $this->assertContains('post_logout_redirect_uri=REDIRECT_URL', $url);
  }

  /**
   * @vcr configuration_openid
   */
  public function testCreateAutologinJwt()
  {
    $authorization = $this->getValidAuthorization();

    $tokenSet = new \P7\SSO\TokenSet([
      'access_token' => 'ACCESS_TOKEN',
      'id_token' => 'ID_TOKEN_TOKEN',
      'expires_in' => 1234,
      'received_at' => 5678
    ]);

    $loginToken = JWT::decode($authorization->createAutologinJwt($tokenSet),
      $authorization->getConfig()->client_secret, ['HS256']);

    $this->assertEquals((object)[
      'access_token' => 'ACCESS_TOKEN',
      'id_token' => 'ID_TOKEN_TOKEN',
      'remember_me' => true
    ], $loginToken);
  }

  /**
   * @vcr configuration_openid
   */
  public function testAutologinUri()
  {
    $authorization = $this->getValidAuthorization();

    $tokenSet = new \P7\SSO\TokenSet([
      'access_token' => 'ACCESS_TOKEN',
      'id_token' => 'ID_TOKEN_TOKEN',
      'expires_in' => 1234,
      'received_at' => 5678
    ]);

    $uri = $authorization->autologinUri($tokenSet, [
      'redirect_uri' => 'REDIRECT_URI',
    ], [
      'remember_me' => false,
    ]);

    $config = $authorization->getConfig();

    $parsed = parse_url($uri);
    $this->assertEquals('/connect/v1.0/authorize', $parsed['path']);

    $query = [];
    parse_str($parsed['query'], $query);
    $this->assertArraySubset([
      'client_id' => $config->client_id,
      'redirect_uri' => 'REDIRECT_URI',
      'autologin' => $authorization->createAutologinJwt($tokenSet, ['remember_me' => false])
    ], $query);
  }

  /**
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testCallbackRequiredParamsException()
  {
    $authorization = $this->getValidAuthorization();
    $authorization->callback([]);
  }

  /**
   * @vcr configuration_openid
   */
  public function testCallback()
  {
    $authorization = $this->getValidAuthorizationMock(['getTokens']);

    $authorization->expects($this->once())
      ->method('getTokens')
      ->with($this->anything(), $this->equalTo('authorization_code'));

    $tokens = $authorization->callback([
      'code' => 'CODE',
      'redirect_uri' => 'REDIRECT_URI'
    ]);

  }

  /**
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testRefreshRequiredParamsException()
  {
    $authorization = $this->getValidAuthorization();
    $authorization->refresh([]);
  }

  /**
   * @vcr configuration_openid
   */
  public function testRefresh()
  {
    $authorization = $this->getValidAuthorizationMock(['getTokens']);

    $authorization->expects($this->once())
      ->method('getTokens')
      ->with($this->anything(), $this->equalTo('refresh_token'));

    $tokens = $authorization->refresh([
      'refresh_token' => 'REFRESH_TOKEN'
    ]);

  }

  /**
   * @vcr configuration_openid
   */
  public function testClientCredentials()
  {
    $authorization = $this->getValidAuthorizationMock(['getTokens']);

    $authorization->expects($this->once())
      ->method('getTokens')
      ->with($this->anything(), $this->equalTo('client_credentials'));

    $tokens = $authorization->clientCredentials();
  }

  /**
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testPasswordRequiredParamsException()
  {
    $authorization = $this->getValidAuthorization();
    $authorization->password([]);
  }

  /**
   * @vcr configuration_openid
   */
  public function testPassword()
  {
    $authorization = $this->getValidAuthorizationMock(['getTokens']);

    $authorization->expects($this->once())
      ->method('getTokens')
      ->with($this->anything(), $this->equalTo('password'));

    $tokens = $authorization->password([
      'login' => 'REFRESH_TOKEN',
      'password' => 'PASSWORD'
    ]);

  }

  /**
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testBackofficeRequiredParamsException()
  {
    $authorization = $this->getValidAuthorization();
    $authorization->backoffice([]);
  }

  /**
   * @expectedException P7\SSO\Exception\TokenVerificationException
   *
   * @vcr authorization_decode_id_failed
   */
  public function testDecodeIdTokenFailed() {
    $authorization = $this->getValidAuthorization();
    $tokens = $authorization->refresh([
      'refresh_token' => 'eek9cvfU9JMTOHMMtAbVoKk5g0lm2DxGLRJaJDOQFEuutcVTnvRfeq4C3wvcwcFya8467vk3jdRblwh8ExgmCcySQH32aqYCFAWwZ6SR2c0jlNh7hjBPUQMe',
    ]);
  }

  /**
   * @vcr configuration_openid
   */
  public function testBackoffice()
  {
    $configuration = new Configuration([
      'environment' => 'test',
      'client_id' => 'barbaz',
      'client_secret' => '123',
      'service_id' => 'SERVICE_ID',
      'backoffice_key' => openssl_pkey_get_private('file://' . __DIR__ . '/fixtures/certs/rsa.pem')
    ]);

    $authorization = $this->getValidAuthorizationMock(['getTokens'], $configuration);

    $authorization->expects($this->once())
      ->method('getTokens')
      ->with($this->anything(), $this->equalTo('backoffice_code'));

    $tokens = $authorization->backoffice([
      'account_id' => 'ACCOUNT_ID'
    ]);

  }

  /**
   * @vcr authorization_get_tokens_callback_exception
   * @expectedException P7\SSO\Exception\ApiException
   */
  public function testGetTokensCallbackException()
  {
    $authorization = $this->getValidAuthorization();

    $tokens = $authorization->callback([
      'code' => 'INVALID',
      'redirect_uri' => 'INVALID'
    ]);

  }


  /**
   * @vcr authorization_get_tokens_callback
   */
  public function testGetTokensCallback()
  {
    $authorization = $this->getValidAuthorizationMock(['decodeIdToken']);
    $authorization->method('decodeIdToken')->will($this->returnValue((object)[]));

    $tokens = $authorization->callback([
      'code' => 'dfY642LBAhPt2cGugFsGAJ0ChLp7eYo8wUg1bPrBvNVp3SuUmRx5fxcPVUyWB6TUTJf6FOB3jKZ9D8WH',
      'redirect_uri' => 'http://localhost:8000/callback'
    ]);

    $this->assertInstanceOf('P7\SSO\TokenSet', $tokens);
  }

}
