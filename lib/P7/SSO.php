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
use \Namshi\JOSE\SimpleJWS;

/**
 * @property string $client_id
 * @property string $client_secret
 * @property array $backoffice
 * @property string $environment
 */
class SSO {
  public $config;
  private $authorization_cache;

  function __construct($options) {
    $this->config = new Configuration($options);
  }

  // TODO: implement me!
  // public static function rediscover() {
  // }

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

  public function backoffice($user_id = null, $custom_payload = []) {
    $jws  = new SimpleJWS(array(
        'alg' => 'RS256'
    ));

    $jws->setPayload(array_merge([
        'service_id' => $this->config->client_id,
        'nonce' => Nonce::generate(),
        'timestamp' => time()
    ], $custom_payload));

    $jws->sign($this->config->backoffice_key);

    return new Http([
      'base_uri' => $this->config->host . '/api/accounts/',
      'headers' => [
        'Authorization' => '7Pass-Backoffice ' . $jws->getTokenString()
      ]
    ]);
  }
}
