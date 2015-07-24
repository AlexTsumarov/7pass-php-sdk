<?php
namespace P7\SSO;

class Configuration {
  const DEFAULTS = [
    'environment' => 'production'
  ];

  const ENVIRONMENT_DEFAULTS = [
    'production' => [
      'host' => 'https://sso.7pass.de'
    ], 'qa' => [
      'host' => 'https://sso.qa.7pass.ctf.prosiebensat1.com'
    ], 'development' => [
      'host' => 'http://sso.7pass.dev'
    ], 'test' => [
      'host' => 'http://sso.7pass.dev'
    ]
  ];

  private $data;

  function __construct($options = []) {
    $this->data = array_merge(self::DEFAULTS, $options);
    $this->data = array_merge($this->data, self::ENVIRONMENT_DEFAULTS[$this->environment], $options);
  }

  // Access data from array directly
  public function __get($name)
  {
    return $this->data[$name];
  }

  public function __set($name, $value)
  {
    $this->data[$name] = $value;
  }

  public function __isset($name)
  {
    return isset($this->data[$name]);
  }

  public function __unset($name)
  {
    unset($this->data[$name]);
  }
}
