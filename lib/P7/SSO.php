<?php

namespace P7;

require __DIR__ . '/../../vendor/autoload.php';

use P7\SSO\Authorization;
use P7\SSO\Configuration;
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

  public function api($accessToken) {
    $appsecret = ($this->config->client_secret ? hash_hmac('sha256', $accessToken, $this->config->client_secret) : null);

    return new Http([
      'base_uri' => $this->config->host . '/api/accounts/',
      'headers' => [
        'Authorization' => 'Bearer ' . $accessToken
      ],
      'data' => [
        'appsecret_proof' => $appsecret
      ]
    ]);
  }
}
