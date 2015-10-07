<?php

use \P7\SSO\Configuration;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{

  public function setUp() {

    $this->validParams = [
      'client_id' => '123',
      'client_secret' => '234'
    ];

  }

  protected function createTestConfiguration() {
    $config = $this->validParams;
    $config['environment'] = 'test';
    return new Configuration($config);
  }

  public function testUserAgent() {
    $config = $this->validParams;
    $config['user_agent'] = ':version::os:';
    $c = new Configuration($config);

    $this->assertEquals(Configuration::VERSION . PHP_OS, $c->user_agent);
  }

  public function testSetsCorrectlyDefaults()
  {
    $config = new Configuration($this->validParams);

    $this->assertEquals('production', $config->environment);
    $this->assertEquals('https://sso.7pass.de', $config->host);
  }

  public function testSetsCorrectlyCustomEnvironmentDefaults()
  {
    $config = $this->createTestConfiguration();

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

  /**
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testMissingConfigOption() {
    $config = new Configuration([
      'environment' => 'test',
    ]);

    //try to read unset value
    $config->service_id;
  }

  /**
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testImmutability() {
    $config = $this->createTestConfiguration();
    $config->foo = 'bar';
  }

  /**
   * @vcr configuration_openid
   */
  public function testRediscovery() {
    $config = $this->createTestConfiguration();

    $cacheItem = $this->getMockBuilder('Stash\Item')
      ->setMethods(['set'])
      ->getMock();

    $cacheItem->expects($this->once())
      ->method('set');

    $cachePool = $this->getMockBuilder('Stash\Pool')
      ->setConstructorArgs([new Stash\Driver\Ephemeral()])
      ->setMethods(['getItem'])
      ->getMock();

    $cachePool->expects($this->once())
      ->method('getItem')
      ->will($this->returnValue($cacheItem));

    $config->setCachePool($cachePool);

    $config->rediscover();
  }

  /**
   * @vcr configuration_openid
   */
  public function testGetOpenIdConfigCache() {
    $config = $this->createTestConfiguration();

    $cacheItem = $this->getMockBuilder('Stash\Item')
      ->setMethods(['get', 'isMiss'])
      ->getMock();

    $cacheItem->expects($this->once())
      ->method('isMiss')
      ->will($this->returnValue(false));

    $cacheItem->expects($this->once())
      ->method('get');

    $cachePool = $this->getMockBuilder('Stash\Pool')
      ->setConstructorArgs([new Stash\Driver\Ephemeral()])
      ->setMethods(['getItem'])
      ->getMock();

    $cachePool->expects($this->once())
      ->method('getItem')
      ->will($this->returnValue($cacheItem));

    $config->setCachePool($cachePool);

    $config->getOpenIdConfig();
  }

}
