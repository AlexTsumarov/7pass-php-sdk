<?php
namespace P7\SSO;

use P7\SSO\Exception\BadRequestException;
use P7\SSO\Exception\InvalidArgumentException;
use P7\SSO\Exception\TokenVerificationException;
use \Firebase\JWT\JWT;

class Authorization {
  private $config;

  function __construct(Configuration $config) {
    $this->config = $config;
  }

  public function uri(array $data) {
    $this->validateParams($data, ['redirect_uri']);

    $data = array_merge([
      'response_type' => 'code',
      'client_id' => $this->config->client_id,
      'scope' => 'openid profile email',
      'nonce' => Nonce::generate()
    ], $data);

    return $this->config->host . '/connect/v1.0/authorize?' . http_build_query($data);
  }

  public function callback($data) {
    $this->validateParams($data, ['redirect_uri']);

    return $this->getTokens($data, 'authorization_code');
  }

  public function refresh(array $data) {
    $this->validateParams($data, ['refresh_token']);

    return $this->getTokens($data, 'refresh_token');
  }

  public function password(array $data) {
    $this->validateParams($data, ['login', 'password']);

    $data = array_merge([
      'client_id' => $this->config->client_id,
      'scope' => 'openid profile email'
    ], $data);

    return $this->getTokens($data, 'password');
  }

  public function backoffice($data) {
    $this->validateParams($data, ['account_id']);

    $jwt = JWT::encode(array_merge([
        'service_id' => $this->config->service_id,
        'nonce' => Nonce::generate(),
        'timestamp' => time()
    ], $data), $this->config->backoffice_key, 'RS256');

    $data = [
      'code' => $jwt,
      'scope' => 'openid'
    ];

    return $this->getTokens($data, 'backoffice_code');
  }

  protected function decodeIdToken($token) {
    try {
      return JWT::decode($token, $this->config->getKeys(), ['RS256']);
    } catch(\Exception $e) {
      throw new TokenVerificationException("ID token verification failed - " . $e->getMessage(), 0, $e);
    }
  }

  protected function getTokens($params, $grant_type) {
    $params['grant_type'] = $grant_type;

    $client = new Http([
      'base_uri' => $this->config->host,
      'auth' => [$this->config->client_id, $this->config->client_secret]
    ]);

    $res = $client->post('/connect/v1.0/token', $params);

    if(!$res->success) {
      throw new BadRequestException($res->error->message . ' - ' . $res->error->description);
    }

    $data = $res->data;

    // Validates ID token signature
    $data->id_token_decoded = $this->decodeIdToken($data->id_token);

    return $data;
  }

  protected function validateParams(array $data, array $params) {
    foreach($params as $param) {
      if(empty($data[$param])) {
        throw new InvalidArgumentException('Missing param: ' . $param);
      }
    }
  }
}
