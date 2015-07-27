<?php
namespace P7\SSO;

class Nonce {
  static function generate() {
    return base64_encode(openssl_random_pseudo_bytes(32));
  }
}
