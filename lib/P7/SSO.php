<?php

namespace P7;

require __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use P7\SSO\Authorization;
use P7\SSO\Configuration;
use P7\SSO\Exception\InvalidArgumentException;
use P7\SSO\Nonce;
use P7\SSO\TokenSet;
use Requests;
use Requests_Auth_Basic;
use P7\SSO\Http;
use P7\SSO\Session;

class SSO {
  const VERSION = '1.0.0';

  protected $config;
  protected $authorizationCache;

  function __construct(Configuration $config) {
    $this->config = $config;
  }

  public function authorization() {
    if($this->authorizationCache) {
      return $this->authorizationCache;
    } else {
      return $this->authorizationCache = new Authorization($this->config);
    }
  }

  public function accountClient($accessToken) {
    if($accessToken instanceof TokenSet) {
      $accessToken = $accessToken->access_token;
    }

    if(empty($accessToken)) {
      throw new InvalidArgumentException('AccessToken is undefined');
    }

    $appsecret = ($this->config->client_secret ? hash_hmac('sha256', $accessToken, $this->config->client_secret) : null);

    return new Http([
      'base_uri' => $this->config->host . '/api/accounts/',
      'headers' => [
        'Authorization' => 'Bearer ' . $accessToken
      ],
      'query' => [
        'appsecret_proof' => $appsecret
      ]
    ]);
  }

  public function backofficeClient(array $customPayload = []) {
    $key = $this->config->backoffice_key;

    $jwt = JWT::encode(array_merge([
      'service_id' => $this->config->service_id,
      'nonce' => Nonce::generate(),
      'timestamp' => time()
    ], $customPayload), $key, 'RS256');

    return new Http([
      'base_uri' => $this->config->host . '/api/backoffice/',
      'headers' => [
        'Authorization' => '7Pass-Backoffice ' . $jwt
      ]
    ]);
  }

  public function client(array $params = []) {
    return new Http(array_merge([
      'base_uri' => $this->config->host . '/api/',
    ], $params));
  }

  public function getConfig() {
    return $this->config;
  }

}
