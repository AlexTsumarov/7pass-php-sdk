<?php

use \P7\SSO\Authorization;
use \P7\SSO\Configuration;

class AuthorizationTest extends PHPUnit_Framework_TestCase
{
  public function testGeneratesUri()
  {
    $config = new Configuration(['environment' => 'test', 'client_id' => 'barbaz', 'client_secret' => '123']);
    $authorization = new Authorization($config);

    $this->assertEquals('http://sso.7pass.dev/connect/v1.0/authorize?response_type=code&client_id=barbaz&scope=openid+profile+email&redirect_uri=REDIRECT&nonce=foobar',
      $authorization->uri([
        'redirect_uri' => 'REDIRECT',
        'nonce' => 'foobar'
      ]));
  }
}
