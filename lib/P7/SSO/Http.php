<?php
namespace P7\SSO;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use P7\SSO;

class Http {
  private $options;

  public static $JSON_PAYLOAD_METHODS = ['POST', 'PUT'];

  function __construct(array $options = []) {

    $this->options = array_merge_recursive([
      'http_errors' => false,
      'headers' => [
        'User-Agent' => '7P-SDK-PHP/' . SSO::VERSION . ' (' . PHP_OS . ')',
        'Accept' => 'application/json'
      ],
      'data' => []
    ], $options);

  }

  private function request($method, $url, $data = []) {
    $url = ltrim($url, '/');
    $request = new Request($method, $url);

    $data_merged = array_merge_recursive($this->options['data'], $data);

    $opts = [];

    if(!empty($data_merged)) {
      if(in_array($method, self::$JSON_PAYLOAD_METHODS)) {
        $opts['json'] = $data_merged;
      } else {
        $opts['query'] = $data_merged;
      }
    }

    try {

      $client = new Client($this->options);
      $response = $client->send($request, $opts);

      echo var_dump($url);
      echo var_dump($opts);
      echo var_dump($response->getStatusCode());

      return Response::fromGuzzlehttpResponse($response);

    } catch(RequestException $e) {
      throw new SSO\Exception\HttpException($e->getMessage(), $e->getCode(), $e);
    }
  }

  public function get($url, $data = []) {
    return $this->request('GET', $url, $data);
  }

  public function post($url, $data = []) {
    return $this->request('POST', $url, $data);
  }

  public function patch($url, $data = []) {
    return $this->request('PATCH', $url, $data);
  }

  public function put($url, $data = []) {
    return $this->request('PUT', $url, $data);
  }

  public function delete($url, $data = []) {
    return $this->request('DELETE', $url, $data);
  }
}
