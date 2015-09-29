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
      'data' => [],
      'query' => []
    ], $options);

  }

  private function request($method, $url, $data = []) {
    $url = ltrim($url, '/');

    $request = new Request($method, $url);

    $dataMerged = array_merge_recursive($this->options['data'], $data);

    $opts = [
      'query' => $this->options['query']
    ];

    if(!empty($dataMerged)) {
      if(in_array($method, self::$JSON_PAYLOAD_METHODS)) {
        $opts['json'] = $dataMerged;
      } else {
        $opts['query'] = array_merge($opts['query'], $dataMerged);
      }
    }

    try {

      $client = new Client($this->options);
      $response = $client->send($request, $opts);

      return Response::fromGuzzlehttpResponse($response);

    } catch(RequestException $e) {
      throw new SSO\Exception\HttpException($e->getMessage(), $e->getCode(), $e);
    }
  }

  public function batch(array $data) {

    $json = [];
    foreach($data as $key => $url) {
      $json[$key] = [
        'url' => '/api/accounts/' . ltrim($url, '/')
      ];
    }

    $res = $this->request('POST', '/batch', $json);

    if(!$res->success) {
      return $res;
    }

    $ret = [];
    foreach($res->data as $key => $singleResponse) {
      $ret[$key] = json_decode($singleResponse->body);
    }

    return $ret;
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
