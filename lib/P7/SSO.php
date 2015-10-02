<?php

namespace P7;

require __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use P7\SSO\Authorization;
use P7\SSO\Configuration;
use P7\SSO\Exception\InvalidArgumentException;
use P7\SSO\Nonce;
use P7\SSO\TokenSet;
use P7\SSO\ApiClient;
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
    if(empty($accessToken)) {
      throw new InvalidArgumentException('AccessToken is undefined');
    }

    return $this->client($accessToken, [
      'base_uri' => '/api/accounts/',
    ]);
  }

  public function backofficeClient(array $customPayload = []) {
    $key = $this->config->backoffice_key;

    $jwt = JWT::encode(array_merge([
      'service_id' => $this->config->service_id,
      'nonce' => Nonce::generate(),
      'timestamp' => time()
    ], $customPayload), $key, 'RS256');

    return new ApiClient([
      'host' => $this->config->host,
      'base_uri' => '/api/backoffice/',
      'headers' => [
        'Authorization' => '7Pass-Backoffice ' . $jwt
      ]
    ]);
  }

  public function client($accessToken = null, array $params = []) {

    $clientParams = array_merge([
      'host' => $this->config->host,
    ], $params);

    if($accessToken instanceof TokenSet) {
      $accessToken = $accessToken->access_token;
    }

    if(!empty($accessToken)) {
      $appsecret = ($this->config->client_secret ? hash_hmac('sha256', $accessToken, $this->config->client_secret) : null);

      $clientParams = array_merge_recursive($clientParams, [
        'headers' => [
          'Authorization' => 'Bearer ' . $accessToken
        ],
        'query' => [
          'appsecret_proof' => $appsecret
        ]
      ]);

    }

    return new ApiClient($clientParams);
  }

  public function getConfig() {
    return $this->config;
  }

}
