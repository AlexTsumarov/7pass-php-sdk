<?php

use \P7\SSO\Configuration;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{
  protected $requiredParams = [
    'client_id' => '123',
    'client_secret' => '234',
  ];

  protected function createConfiguration() {
    return new Configuration($this->requiredParams);
  }

  public function requiredParams() {
    return [['client_id'], ['client_secret']];
  }

  /**
   * @dataProvider requiredParams
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testRequiredArguments($missingParam) {
    $allParams = $this->requiredParams;

    unset($allParams[$missingParam]);

    new Configuration($allParams);
  }

  public function testSetsCorrectlyDefaults()
  {
    $config = new Configuration($this->requiredParams);

    $this->assertEquals('production', $config->environment);
    $this->assertEquals('https://sso.7pass.de', $config->host);
  }

  public function testSetsCorrectlyCustomEnvironmentDefaults()
  {
    $config = new Configuration(array_merge($this->requiredParams, ['environment' => 'test']));

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

  public function testSetsUnsetsCorrectly() {
    $config = new Configuration($this->requiredParams);
    $config->foo = 'bar';

    $this->assertEquals(true, isset($config->foo));
    $this->assertEquals('bar', $config->foo);

    unset($config->foo);

    $this->assertEquals(false, isset($config->foo));
  }
}
