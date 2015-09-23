<?php
namespace P7\SSO;

use P7\SSO\Exception\HttpException;
use P7\SSO\Exception\OpenIdConfigurationException;

class Configuration {
  public static $DEFAULTS = [
    'environment' => 'production'
  ];

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

  function __construct($options = []) {

    $mandatoryOptions = ['client_id', 'client_secret'];

    if(!empty($options['backoffice_key']) || !empty($options['service_id'])) {
      $mandatoryOptions = array_merge($mandatoryOptions, ['backoffice_key', 'service_id']);
    }

    foreach($mandatoryOptions as $mandatoryOption) {
      if(empty($options[$mandatoryOption])) {
        throw new Exception\InvalidArgumentException("Config option required: " . $mandatoryOption);
      }
    }

    $this->data = array_merge(self::$DEFAULTS, $options);
    $this->data = array_merge($this->data, self::$ENVIRONMENT_DEFAULTS[$this->environment], $options);
  }

  public function rediscover($now = false) {
    if($now) {
      $this->getOpenIdConfiguration(true);
      return;
    }

    $this->getCachePool()->flush();
  }

  public function getOpenIdConfiguration($refresh = false) {
    $item = $this->getCachePool()->getItem(['config', 'openid', $this->environment]);

    $config = $item->get();

    if($refresh || $item->isMiss()) {
      $item->lock();

      $config = $this->fetchOpenIdConfiguration();

      $item->set($config);
    }

    return $config;
  }

  public function getKeys() {
    return $this->getOpenIdConfiguration()->keys;
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

  protected function fetchOpenIdConfiguration() {

    try {
      $client = new Http([
        'base_uri' => $this->host,
        'http_errors' => true
      ]);

      $res = $client->get('.well-known/openid-configuration');
      if(!$res->success) {
        throw new OpenIdConfigurationException('OpenID configuration can not be fetched');
      }

      $config = $res->data;

      $res = $client->get($config->jwks_uri);
      $jwks = $res->data->keys;

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

    } catch (HttpException $e) {
      throw new OpenIdConfigurationException('OpenID configuration can not be fetched', 0, $e);
    }

  }

  // Access data from array directly
  public function __get($name)
  {
    if(!$this->__isset($name)) {
      throw new Exception\InvalidArgumentException("No config value set: " . $name);
    }

    return $this->data[$name];
  }

  public function __set($name, $value)
  {
    $this->data[$name] = $value;
  }

  public function __isset($name)
  {
    return isset($this->data[$name]);
  }

  public function __unset($name)
  {
    unset($this->data[$name]);
  }
}
