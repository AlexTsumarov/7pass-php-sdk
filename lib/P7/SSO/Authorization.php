<?php
namespace P7\SSO;

use \P7\SSO\Nonce;
use \P7\SSO\Http;

class Authorization {
  private $config;

  function __construct($config) {
    $this->config = $config;
  }

  public function uri($options) {
    $default_options = [
      'response_type' => 'code',
      'client_id' => $this->config->client_id,
      'scope' => 'openid profile email',
      'nonce' => Nonce::generate()
    ];

    $data = array_merge($default_options, $options);

    // Validate redirect_uri is present
    return $this->config->host . '/connect/v1.0/authorize?' . http_build_query($data);
  }

  private function getTokens($data, $grant_type) {
    $data['grant_type'] = $grant_type;

    $client = new Http([
      'base_uri' => $this->config->host,
      'auth' => [$this->config->client_id, $this->config->client_secret]
    ]);

    return $client->post('/connect/v1.0/token', $data);
  }

  public function callback($data) {
    return $this->getTokens($data, 'authorization_code');
  }

  public function refresh($data) {
    return $this->getTokens($data, 'refresh_token');
  }
}
