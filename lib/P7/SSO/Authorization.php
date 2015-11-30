<?php
namespace P7\SSO;

use P7\SSO\Exception\InvalidArgumentException;
use P7\SSO\Exception\TokenSignatureException;
use P7\SSO\Exception\TokenVerificationException;
use \Firebase\JWT\JWT;

class Authorization {
  private $config;

  function __construct(Configuration $config) {
    $this->config = $config;
  }

  public function getConfig() {
    return $this->config;
  }

  public function authorizeUri(array $data) {
    $this->validateParams($data, ['redirect_uri']);

    $data = array_merge([
      'response_type' => 'code',
      'client_id' => $this->config->client_id,
      'scope' => $this->config->default_scopes,
    ], $data);

    if(empty($data['nonce']) && strpos($data['response_type'], 'id_token') !== false) {
      $data['nonce'] = Nonce::generate();
    }

    return $this->config->getOpenIdConfig()->authorization_endpoint . '?' . http_build_query($data);
  }

  public function logoutUri(array $data) {
    $this->validateParams($data, ['id_token_hint', 'post_logout_redirect_uri']);

    if($data['id_token_hint'] instanceof TokenSet) {
      $data['id_token_hint'] = $data['id_token_hint']->id_token;
    }

    return $this->config->getOpenIdConfig()->end_session_endpoint . '?' . http_build_query($data);
  }

  public function callback(array $data) {
    $this->validateParams($data, ['code', 'redirect_uri']);

    return $this->getTokens($data, 'authorization_code');
  }

  public function refresh(array $data) {
    if($data instanceof TokenSet) {
      $data = [
        'refresh_token' => $data->refresh_token
      ];
    }

    $this->validateParams($data, ['refresh_token']);

    return $this->getTokens($data, 'refresh_token');
  }

  public function password(array $data) {
    $this->validateParams($data, ['login', 'password']);

    $data = array_merge([
      'client_id' => $this->config->client_id,
      'scope' => $this->config->default_scopes
    ], $data);

    return $this->getTokens($data, 'password');
  }

  public function clientCredentials(array $data = []) {
    return $this->getTokens($data, 'client_credentials');
  }

  public function backoffice(array $data) {
    $this->validateParams($data, ['account_id']);

    try {
      $jwt = JWT::encode([
          'service_id' => $this->config->service_id,
          'account_id' => $data['account_id'],
          'nonce' => Nonce::generate(),
          'timestamp' => time()
      ], $this->config->backoffice_key, 'RS256');

      unset($data['account_id']);

      //defaults
      $data = array_merge([
        'code' => $jwt,
        'scope' => $this->config->default_scopes
      ], $data);

      return $this->getTokens($data, 'backoffice_code');
    } catch(\DomainException $e) {
      throw new TokenSignatureException('Backoffice JWT token could not be signed', 0, $e);
    }
  }

  public function createAutologinJwt(TokenSet $tokenSet, array $data = []) {
    if(empty($tokenSet->id_token)) {
      throw new InvalidArgumentException('Missing id_token');
    }

    $data = array_merge([
      'remember_me' => false,
      'access_token' => $tokenSet->access_token,
      'id_token' => $tokenSet->id_token
    ], $data);

    return JWT::encode($data, $this->config->client_secret, 'HS256');
  }

  public function autologinUri(TokenSet $tokenSet, array $params, array $tokenData = []) {
    $this->validateParams($params, ['redirect_uri']);

    $params = array_merge([
      'client_id' => $this->config->client_id,
    ], $params);

    $jwt = $this->createAutologinJwt($tokenSet, $tokenData);
    $params['autologin'] = $jwt;

    return $this->config->getOpenIdConfig()->authorization_endpoint . '?' . http_build_query($params);
  }

  protected function decodeIdToken($token) {
    try {
      return JWT::decode($token, $this->config->getKeys(), ['RS256']);
    } catch(\Exception $e) {
      throw new TokenVerificationException("ID token verification failed - " . $e->getMessage(), 0, $e);
    }
  }

  protected function getTokens(array $params, $grantType) {
    $params['grant_type'] = $grantType;

    $client = $this->createApiClient();

    $data = $client->post($this->config->getOpenIdConfig()->token_endpoint, $params);

    // Validates ID token signature if token available
    if(!empty($data->id_token)) {
      $data->id_token_decoded = $this->decodeIdToken($data->id_token);
    }

    return TokenSet::receiveTokens($data);
  }

  protected function createApiClient() {
    return new ApiClient([
      'user_agent' => $this->config->user_agent,
      'host' => $this->config->host,
      'auth' => [$this->config->client_id, $this->config->client_secret]
    ]);
  }

  protected function validateParams(array $data, array $params) {
    foreach($params as $param) {
      if(empty($data[$param])) {
        throw new InvalidArgumentException('Missing param: ' . $param);
      }
    }
  }
}
