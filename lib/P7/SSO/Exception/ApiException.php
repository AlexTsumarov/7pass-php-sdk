<?php

namespace P7\SSO\Exception;


class ApiException extends HttpException implements SSOException {

  protected $error;

  public function __construct($message, $code, $error) {
    parent::__construct($message, $code);

    $this->error = $error;
  }

  public function getError() {
    return $this->error;
  }

} 