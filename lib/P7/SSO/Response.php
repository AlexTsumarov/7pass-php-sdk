<?php
namespace P7\SSO;

class Response {
  public $code;
  public $raw;
  public $error;
  public $data;
  public $success;

  function __construct($code, $raw) {
    $this->code = $code;
    $this->raw = $raw;

    $result = json_decode($raw);

    if(isset($result->error)) {
      $error = $result->error;

      if(is_object($error)) {
        $ret = [
          'message' => $error->message,
          'description' => (isset($error->detail) ? $error->detail : null)
          // TODO: + status ?
        ];
      } else {
        $ret = [
          'message' => $error,
          'description' => $result->error_description
        ];
      }

      $this->error = (object)$ret;
      $this->success = false;
    } else {
      $this->data = (isset($result->data) ? $result->data : $result);
      $this->success = true;
    }
  }

  static function fromGuzzlehttpResponse($r) {
    return new self($r->getStatusCode(), $r->getBody());
  }
}