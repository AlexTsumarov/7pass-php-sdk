<?php

use \P7\SSO\Authorization;
use \P7\SSO\Configuration;

class AuthorizationTest extends PHPUnit_Framework_TestCase
{
  public function testGeneratesUri()
  {
    $config = new Configuration(['environment' => 'test', 'client_id' => 'barbaz']);
    $authorization = new Authorization($config);

    $this->assertEquals('http://sso.7pass.dev/connect/v1.0/authorize?response_type=code&client_id=barbaz&scope=openid+profile+email&nonce=foobar', $authorization->uri(['nonce' => 'foobar']));
  }
}
