<?php

namespace P7;

require __DIR__ . '/../../vendor/autoload.php';

use \Requests;
use \Requests_Auth_Basic;
use \P7\SSO\Authorization;
use \P7\SSO\Configuration;
use \P7\SSO\Http;
use \P7\SSO\Session;
use \P7\SSO\Nonce;
use \Firebase\JWT\JWT;

/**
 * @property string $client_id
 * @property string $client_secret
 * @property array $backoffice
 * @property string $environment
 */
class SSO {
  public $config;
  private $authorization_cache;
  private static $cachePool;

  public static function cache() {
    // Cache to APC by default if available, if not - cache to the tmp directory
    if(!isset(self::$cachePool)) {
      if(\Stash\Driver\Apc::isAvailable()) {
        $driver = new \Stash\Driver\Apc();
      } else {
        $driver = new \Stash\Driver\FileSystem();
      }

      $driver->setOptions();
      self::$cachePool = new \Stash\Pool($driver);
    }

    return self::$cachePool;
  }

  function __construct($options) {
    $this->config = new Configuration($options);

    if(!isset($this->config->jwks)) {
      $this->config->jwks = $this->fetchJwks();
    }
  }

  private function base64_from_url($base64url) {
    return strtr($base64url, '-_', '+/');
  }

  private function fetchJwks() {
    $item = self::cache()->getItem(['config', 'jwks', $this->config->environment]);

    $data = $item->get();

    if($item->isMiss()) {
      $item->lock();

      $client = new Http([
        'base_uri' => $this->config->host,
        'http_errors' => true
      ]);

      $res = $client->get('.well-known/openid-configuration');
      $jwks_uri = $res->data->jwks_uri;

      $res = $client->get($jwks_uri);
      $keys = $res->data->keys;
      $data = [];

      $rsa = new \Crypt_RSA();

      foreach($keys as $key) {
        $public = '<RSAKeyValue>
                     <Modulus>'.$this->base64_from_url($key->n).'</Modulus>
                     <Exponent>'.$this->base64_from_url($key->e).'</Exponent>
                   </RSAKeyValue>';
        $rsa->loadKey($public, CRYPT_RSA_PUBLIC_FORMAT_XML);
        $rsa->setPublicKey();

        $data[$key->kid] = $rsa->getPublicKey();
      }

      $item->set($data);
    }

    return $data;
  }

  public function authorization() {
    if($this->authorization_cache) {
      return $this->authorization_cache;
    } else {
      return $this->authorization_cache = new Authorization($this->config);
    }
  }

  public function api($access_token) {
    $appsecret = ($this->config->client_secret ? hash_hmac('sha256', $access_token, $this->config->client_secret) : null);

    return new Http([
      'base_uri' => $this->config->host . '/api/accounts/',
      'headers' => [
        'Authorization' => 'Bearer ' . $access_token
      ],
      'data' => [
        'appsecret_proof' => $appsecret
      ]
    ]);
  }
}
