<?php
namespace P7\SSO;

use P7\SSO\Exception\InvalidArgumentException;
use P7\SSO\Exception\OpenIdConfigurationException;
use P7\SSO;
use Stash\Interfaces\PoolInterface;

class Configuration {

  const VERSION = '1.0.0';

  public static $ENVIRONMENT_DEFAULTS = [
    'production' => [
      'host' => 'https://sso.7pass.de'
    ], 'qa' => [
      'host' => 'https://sso.qa.7pass.ctf.prosiebensat1.com'
    ], 'development' => [
      'host' => 'http://sso.7pass.dev'
    ], 'test' => [
      'host' => 'http://sso.7pass.dev'
    ]
  ];

  protected $cachePool;
  protected $data;

  function __construct(array $options) {

    $defaults = [
      'environment' => 'production',
      'user_agent' => '7Pass-SDK-PHP/:version: (:os:)',
      'default_scopes' => 'openid profile email'
    ];

    $this->data = array_merge($defaults, $options);
    $this->data = array_merge($this->data, self::$ENVIRONMENT_DEFAULTS[$this->environment], $options);

    $replaceWith = [
      ':version:' => self::VERSION,
      ':os:' => PHP_OS
    ];

    $this->data['user_agent'] = str_replace(array_keys($replaceWith), array_values($replaceWith), $this->data['user_agent']);
  }

  public function rediscover() {
    return $this->getOpenIdConfig(true);
  }

  public function getOpenIdConfig($refresh = false) {
    $item = $this->getCachePool()->getItem(['config', 'openid', $this->environment]);

    if(!$refresh && !$item->isMiss()) {
      return $item->get();
    }

    $item->lock();

    $config = $this->fetchOpenIdConfig();

    $item->set($config);

    return $config;
  }

  public function getKeys() {
    return $this->getOpenIdConfig()->keys;
  }

  public function getCachePool() {

    if(!isset($this->cachePool)) {
      // Cache to APC by default if available, if not - cache to the tmp directory
      if(\Stash\Driver\Apc::isAvailable()) {
        $driver = new \Stash\Driver\Apc();
      } else {
        $driver = new \Stash\Driver\FileSystem();
      }

      $this->cachePool = new \Stash\Pool($driver);
    }

    return $this->cachePool;
  }

  public function setCachePool(PoolInterface $cachePool) {
    $this->cachePool = $cachePool;
  }

  protected function base64_from_url($base64url) {
    return strtr($base64url, '-_', '+/');
  }

  protected function fetchOpenIdConfig() {

    try {
      $apiClient = $this->getApiClient();
      $config = $apiClient->get('.well-known/openid-configuration');

      $jwkRes = $apiClient->get($config->jwks_uri);
      $jwks = $jwkRes->keys;

      $keys = [];

      $rsa = new \Crypt_RSA();

      foreach($jwks as $key) {
        //if x509 key is available, we don't need to generate it below.
        if(!empty($key->x_509)) {
          $keys[$key->kid] = $key->x_509;
          continue;
        }

        $public = '<RSAKeyValue>
                     <Modulus>'.$this->base64_from_url($key->n).'</Modulus>
                     <Exponent>'.$this->base64_from_url($key->e).'</Exponent>
                   </RSAKeyValue>';
        $rsa->loadKey($public, CRYPT_RSA_PUBLIC_FORMAT_XML);
        $rsa->setPublicKey();

        $keys[$key->kid] = $rsa->getPublicKey();
      }

      $config->keys = $keys;

      return $config;

    } catch (SSO\Exception\HttpException $e) {
      throw new OpenIdConfigurationException('OpenID configuration can not be fetched', 0, $e);
    }

  }

  protected function getApiClient() {
    return new ApiClient([
      'user_agent' => $this->user_agent,
      'host' => $this->host
    ]);
  }

  // Access data from array directly
  public function __get($name)
  {
    if(!$this->__isset($name)) {
      throw new Exception\InvalidArgumentException("No config value set: " . $name);
    }

    return $this->data[$name];
  }

  public function __isset($name)
  {
    return isset($this->data[$name]);
  }

  public function __set($name, $value) {
    throw new InvalidArgumentException("Configuration is immutable");
  }

  public function __unset($name)
  {
    throw new InvalidArgumentException("Configuration is immutable");
  }

}
