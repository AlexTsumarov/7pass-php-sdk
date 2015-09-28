<?php

namespace P7\SSO;

use P7\SSO\Exception\InvalidArgumentException;

class TokenSet extends \ArrayObject {

  public function __construct($data) {
    $data = (object)$data;

    if(!$data->access_token) {
      throw new InvalidArgumentException('TokenSet access_token parameter missing');
    }

    if(!$data->expires_in) {
      throw new InvalidArgumentException('TokenSet expires_in parameter missing');
    }

    if(!$data->received_at) {
      throw new InvalidArgumentException('TokenSet received_at parameter missing');
    }

    if(!$data->refresh_token) {
      throw new InvalidArgumentException('TokenSet refresh_token parameter missing');
    }

    parent::__construct($data, \ArrayObject::ARRAY_AS_PROPS);
  }

  public static function receiveTokens($tokens) {
    $tokens->received_at = time();

    return new self($tokens);
  }

  /**
   * Checks if access token is expired or due to expire (60secs)
   * @return bool
   */
  public function isAccessTokenExpired() {
    return ($this->received_at + $this->expires_in + 60) < time();
  }
} 