<?php

use \P7\SSO;
use \P7\SSO\Http;
use \GuzzleHttp\Client;

class SSOTest extends PHPUnit_Framework_TestCase
{

  public function setUp() {
    $this->code = 'RnbV8u1CWoljG2yV2k3VTdLZ2lxcckUGV9NOAL9xOd7bPAuoAn07UTEv3qfD0YUXQfxfzyv2xsSAl0nO';
    $this->redirectUri = 'http://localhost:8000/callback';
    $this->serverKid = '4cee9dc4d2aaf2eb997113d6b76dc6fe';
    $this->serverPublicKey = "-----BEGIN PUBLIC KEY-----\r\n"
      . "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCtsObxWfrFIGbxahs3YM4AvCbd\r\n"
      . "9gPo8WL6WhHUH+8kgS44TNnzguGK3pPPM87XdF1E3GCyBqhNDt/Y2KogSqeTTnra\r\n"
      . "pXfAXip7ZN1VyMkibPZ3VtaAuIED66B71UyU8eW+hCgB+pGMFWtsK7X4A08yCyVP\r\n"
      . "lstPE6F7Cg2zgKIRXwIDAQAB\r\n"
      . "-----END PUBLIC KEY-----";

    $this->defaultConfig = [
      'client_id' => '54523ed2d3d7a3b4333a9426',
      'client_secret' => 'd7078d0b804522d6c28677d826e39879122c7a80214cc9bfa60be6022f503fec',
      'environment' => 'test',
      'backoffice_key' => file_get_contents(__DIR__ . '/fixtures/certs/rsa.pem'),
      'service_id' => '123',
    ];

    $this->validTokenSet = SSO\TokenSet::receiveTokens([
      'received_at' => 1443685260,
      'access_token' => '1oNtGE1P69OjbRT4bArC21IL5seKBWYTNhueYEDIrYJTiCN5hKNsIFVZSK71Uudhl7ZSzZXuvXuj41Al54uPVLrivy',
      'token_type' => 'Bearer',
      'refresh_token' => 'eB2nHuqYS7mE4mw4aRDr11CZBipW03b14zOAMxlvCxA2SdyB3ZNozuAuEt8i0aIryfE1oyASm1snvl5y6q3UYaBcVXR4UwLK80s5EVngKEf6VPKzbwidKenY',
      'expires_in' => 7200,
      'id_token' => 'ID TOKEN',
      'id_token_decoded' => (object)[
          'sub' => '55e6f55c5925bcfb25c98a4f'
          //other params
        ]
    ]);
  }

  protected function validSSO() {
    $config = new SSO\Configuration($this->defaultConfig);
    $config->setCachePool(new Stash\Pool(new Stash\Driver\Ephemeral()));
    return new SSO($config);
  }

  public function testAuthorizationCache()
  {
    $sso = $this->validSSO();
    $authorization1 = $sso->authorization();
    $authorization2 = $sso->authorization();

    $this->assertSame($authorization1, $authorization2);
  }

  public function testAuthorization()
  {
    $sso = $this->validSSO();

    $authorization = $sso->authorization();
    $this->assertInstanceOf('P7\SSO\Authorization', $authorization);
  }

  public function testAccountClientWithTokenSet()
  {
    $sso = $this->validSSO();

    $client = $sso->accountClient($this->validTokenSet);
    $this->assertInstanceOf('P7\SSO\ApiClient', $client);
  }

  public function testAccountClientWithTokenString()
  {
    $sso = $this->validSSO();

    $accessToken = $this->validTokenSet->access_token;

    $client = $sso->accountClient($accessToken);
    $this->assertInstanceOf('P7\SSO\ApiClient', $client);
    $this->assertEquals('Bearer ' . $accessToken, $client->getOptions()['headers']['Authorization']);
  }

  public function testClientCredentialsClientWithTokenSet()
  {
    $sso = $this->validSSO();

    $client = $sso->clientCredentialsClient($this->validTokenSet);
    $this->assertInstanceOf('P7\SSO\ApiClient', $client);
  }

  public function testBackofficeClient()
  {
    $sso = $this->validSSO();

    $client = $sso->backofficeClient();
    $this->assertInstanceOf('P7\SSO\ApiClient', $client);

    $this->assertContains('7Pass-Backoffice', $client->getOptions()['headers']['Authorization']);
  }
}
