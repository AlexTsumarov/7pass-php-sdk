<?php

use \P7\SSO\Configuration;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{
  public function testSetsCorrectlyDefaults()
  {
    $config = new Configuration();

    $this->assertEquals('production', $config->environment);
    $this->assertEquals('https://sso.7pass.de', $config->host);
  }

  public function testSetsCorrectlyCustomEnvironmentDefaults()
  {
    $config = new Configuration(['environment' => 'test']);

    $this->assertEquals('test', $config->environment);
    $this->assertEquals('http://sso.7pass.dev', $config->host);
  }

  public function testMergesOptions()
  {
    $config = new Configuration([
      'environment' => 'test',
      'host' => 'http://example.com',
      'client_id' => 'foo',
      'client_secret' => 'bar'
    ]);

    $this->assertEquals('test', $config->environment);
    $this->assertEquals('http://example.com', $config->host);
    $this->assertEquals('foo', $config->client_id);
    $this->assertEquals('bar', $config->client_secret);
  }
}
