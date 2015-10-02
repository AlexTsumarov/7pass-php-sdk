<?php

namespace P7\SSO\Exception;


class ApiException extends HttpException implements SSOException {

  protected $httpResponse;

  public function __construct($message, $code, $httpResponse) {
    parent::__construct($message, $code);

    $this->httpResponse = $httpResponse;
  }

  public function getHttpResponse() {
    return $this->httpResponse;
  }

} 