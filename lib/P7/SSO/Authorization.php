<?php
namespace P7\SSO;

use \P7\SSO\Nonce;
use \P7\SSO\Http;
use \Firebase\JWT\JWT;

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

    $res = $client->post('/connect/v1.0/token', $data);

    if($res->success) {
      // Validates ID token signature
      $this->decodeIdToken($res->data->id_token);
    }

    return $res;
  }

  public function decodeIdToken($token) {
    return JWT::decode($token, $this->config->jwks, ['RS256']);
  }

  public function callback($data) {
    return $this->getTokens($data, 'authorization_code');
  }

  public function refresh($data) {
    return $this->getTokens($data, 'refresh_token');
  }

  public function backoffice($account_id, $custom_payload = []) {
    $jwt = JWT::encode(array_merge([
        'service_id' => $this->config->service_id,
        'account_id' => $account_id,
        'nonce' => Nonce::generate(),
        'timestamp' => time()
    ], $custom_payload), $this->config->backoffice_key, 'RS256');

    $data = [
      'code' => $jwt,
      'scope' => 'openid'
    ];

    return $this->getTokens($data, 'backoffice_code');
  }
}
