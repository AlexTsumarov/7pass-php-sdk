<?php

namespace P7;

require __DIR__ . '/../../vendor/autoload.php';

use \Requests;
use \Requests_Auth_Basic;
use \P7\SSO\Http;
use \P7\SSO\Configuration;
use \P7\SSO\Session;
use \P7\SSO\Response;
use \Namshi\JOSE\SimpleJWS;

/**
 * @property string $client_id
 * @property string $client_secret
 * @property array $backoffice
 * @property string $environment
 */
class SSO {
  public $config;

  function __construct($options) {
    $this->config = new Configuration($options);
  }

  // TODO: implement me!
  // public static function rediscover() {
  // }

  private function nonce() {
    return base64_encode(openssl_random_pseudo_bytes(32));
  }

  public function uri($options) {
    $default_options = [
      'response_type' => 'code',
      'client_id' => $this->config->client_id,
      'scope' => 'openid profile email',
      'nonce' => $this->nonce()
    ];

    $data = array_merge($default_options, $options);

    // Validate redirect_uri is present
    return $this->config->host . '/connect/v1.0/authorize?' . http_build_query($data);
  }

  public function callback($data) {
    $data['grant_type'] = 'authorization_code';

    $client = new Http([
      'base_uri' => $this->config->host,
      'auth' => [$this->config->client_id, $this->config->client_secret]
    ]);

    return $client->post('/connect/v1.0/token', $data);
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

  public function backoffice($user_id = null) {
    $jws  = new SimpleJWS(array(
        'alg' => 'RS256'
    ));

    $jws->setPayload(array(
        'service_id' => $this->config->client_id,
        'nonce' => $this->nonce(),
        'timestamp' => time()
    ));

    $jws->sign($this->config->backoffice_key);

    return new Http([
      'base_uri' => $this->config->host . '/api/accounts/',
      'headers' => [
        'Authorization' => '7Pass-Backoffice ' . $jws->getTokenString()
      ]
    ]);
  }
}
