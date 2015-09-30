<?php

use \P7\SSO\Authorization;
use \P7\SSO\Configuration;

class AuthorizationTest extends PHPUnit_Framework_TestCase
{

  protected function getValidAuthorization($configuration = null) {
    if($configuration === null) {
      $configuration = new Configuration([
        'environment' => 'test',
        'client_id' => 'barbaz',
        'client_secret' => '123'
      ]);
    }

    return new Authorization($configuration);
  }

  protected function getValidAuthorizationMock(array $methods, $configuration = null) {

    if($configuration === null) {
      $configuration = new Configuration([
        'environment' => 'test',
        'client_id' => 'barbaz',
        'client_secret' => '123'
      ]);
    }

    return $this->getMockBuilder('P7\SSO\Authorization')
      ->setMethods($methods)
      ->setConstructorArgs([$configuration])
      ->getMock();
  }

  /**
   * @vcr configuration_openid
   */
  public function testAuthorizeUri()
  {
    $authorization = $this->getValidAuthorization();

    $this->assertEquals('http://sso.7pass.dev/connect/v1.0/authorize?response_type=code&client_id=barbaz&scope=openid+profile+email&redirect_uri=REDIRECT&nonce=foobar',
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
   * @vcr configuration_openid
   * @expectedException P7\SSO\Exception\TokenSignatureException
   */
  public function testBackofficeTokenSignatureException()
  {

    $configuration = new Configuration([
      'environment' => 'test',
      'client_id' => 'barbaz',
      'client_secret' => '123',
      'service_id' => 'SERVICE_ID',
      'backoffice_key' => 'INVALID_KEY'
    ]);

    $authorization = $this->getValidAuthorization($configuration);
    $tokens = $authorization->backoffice([
      'account_id' => 'ACCOUNT_ID'
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
   * @vcr configuration_openid
   */
  public function testGetTokens()
  {
    $this->markTestIncomplete();
    return;

    $configuration = new Configuration([
      'environment' => 'test',
      'client_id' => 'barbaz',
      'client_secret' => '123',
      'service_id' => 'SERVICE_ID',
      'backoffice_key' => openssl_pkey_get_private('file://' . __DIR__ . '/fixtures/certs/rsa.pem')
    ]);

    $authorization = $this->getValidAuthorizationMock(['createHttpClient']);

    $authorization->expects($this->once())
      ->method('createHttpClient')
      ->with($this->anything(), $this->equalTo('backoffice_code'))
      ->will($this->returnValue($cacheItem));;

    $tokens = $authorization->backoffice([
      'account_id' => 'ACCOUNT_ID'
    ]);

  }
}
