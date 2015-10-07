<?php
namespace P7\SSO;

class ApiResponse extends \ArrayObject {
  protected $httpResponse;

  function __construct($data, $httpResponse = null) {
    $this->httpResponse = $httpResponse;

    if(is_array($data)) {
      return parent::__construct($data);
    }

    parent::__construct($data, \ArrayObject::ARRAY_AS_PROPS);
  }

  public function getHttpResponse() {
    return $this->httpResponse;
  }

}