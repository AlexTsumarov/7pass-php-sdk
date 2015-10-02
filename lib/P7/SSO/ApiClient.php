<?php
namespace P7\SSO;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use P7\SSO;
use P7\SSO\Exception\ApiException;

class ApiClient {
  protected $options;
  protected $responseDataParser;
  protected $host;
  protected $baseUri;

  public static $JSON_PAYLOAD_METHODS = ['POST', 'PUT', 'PATCH'];

  function __construct(array $options, $responseDataParser = null) {

    if(empty($options['host'])) {
      throw new SSO\Exception\InvalidArgumentException('ApiClient host param missing');
    }

    $this->host = rtrim($options['host'], '/');

    $this->baseUri = '';
    if(!empty($options['base_uri'])) {
      $this->baseUri = rtrim($options['base_uri'], '/');
      unset($options['base_uri']);
    }

    $this->options = array_merge_recursive([
      'http_errors' => true,
      'headers' => [
        'User-Agent' => '7Pass-SDK-PHP/' . SSO::VERSION . ' (' . PHP_OS . ')',
        'Accept' => 'application/json'
      ],
      'data' => [],
      'query' => []
    ], $options);

    $this->responseDataParser = array($this, 'defaultResponseDataParser');

    if($responseDataParser !== null) {
      if(!is_callable($responseDataParser)) {
        throw new SSO\Exception\InvalidArgumentException('Response parser needs to be callable');
      }

      $this->responseDataParser = $responseDataParser;
    }
  }

  public function defaultResponseDataParser($body) {
    return (isset($body->data) ? $body->data : $body);
  }

  public function batch(array $data) {

    $json = [];
    foreach($data as $key => $url) {
      $json[$key] = [
        'url' => $this->getApiUrl($url, false)
      ];
    }

    $res = $this->request('POST', '/api/accounts/batch', $json);

    $ret = [];
    foreach($res as $key => $singleResponse) {
      $body = json_decode($singleResponse->body);
      $ret[$key] = call_user_func($this->responseDataParser, $body);
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

  public function getOptions() {
    return $this->options;
  }

  protected function getApiUrl($url, $includeHost = true) {
    if(strpos($url, 'http') === 0) {
      if(!$includeHost) {
        return str_replace($this->host, '', $url);
      }

      return $url;
    }

    if($url[0] !== '/') {
      $url = $this->baseUri . '/' . $url;
    }

    if($includeHost) {
      $url = $this->host . $url;
    }

    return $url;
  }

  protected function request($method, $url, $data = []) {
    $url = $this->getApiUrl($url);

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

      return $this->fromHttpResponse($response);

    } catch(RequestException $e) {
      $httpResponse = $e->getResponse();
      $statusCode = $httpResponse->getStatusCode();

      $body = json_decode($httpResponse->getBody());
      if($body === null) {
        throw new SSO\Exception\HttpException($e->getMessage(), $statusCode, $e);
      }

      $responseError = $body->error;
      if(is_object($responseError)) {
        $error = [
          'message' => $responseError->message,
          'description' => (isset($responseError->detail) ? $responseError->detail : null)
        ];
      } else {
        $error = [
          'message' => $responseError,
          'description' => $body->error_description
        ];
      }

      $message = empty($error['description']) ? $error['message'] : $error['message'] . ' - ' . $error['description'];
      throw new ApiException($message, $statusCode, $e);
    }
  }

  protected function fromHttpResponse($httpResponse) {

    $body = json_decode($httpResponse->getBody());

    $data = call_user_func($this->responseDataParser, $body);

    return new ApiResponse($data, $httpResponse);
  }
}
