<?php

use P7\SSO\TokenSet;

class TokenSetTest extends PHPUnit_Framework_TestCase
{

  public function setUp() {

    $this->validTokenArray = [
      'received_at' => time(),
      'access_token' => '1oNtGE1P69OjbRT4bArC21IL5seKBWYTNhueYEDIrYJTiCN5hKNsIFVZSK71Uudhl7ZSzZXuvXuj41Al54uPVLrivy',
      'token_type' => 'Bearer',
      'refresh_token' => 'eB2nHuqYS7mE4mw4aRDr11CZBipW03b14zOAMxlvCxA2SdyB3ZNozuAuEt8i0aIryfE1oyASm1snvl5y6q3UYaBcVXR4UwLK80s5EVngKEf6VPKzbwidKenY',
      'expires_in' => 7200,
      'id_token' => 'ID TOKEN',
      'id_token_decoded' => (object)[
        'sub' => '55e6f55c5925bcfb25c98a4f'
        //other params
      ]
    ];

  }

  /**
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testReceiveTokensArgException() {
    TokenSet::receiveTokens('INVALID');
  }

  public function testReceiveTokensArray() {
    $tokens = TokenSet::receiveTokens($this->validTokenArray);

    $this->assertInstanceOf('P7\SSO\TokenSet', $tokens);
  }

  public function testReceiveTokensObject() {
    $tokens = TokenSet::receiveTokens((object)$this->validTokenArray);

    $this->assertInstanceOf('P7\SSO\TokenSet', $tokens);
  }


  public function requiredConstructorParams() {
    return [['received_at'], ['access_token'], ['expires_in'], ['refresh_token']];
  }

  /**
   * @dataProvider requiredConstructorParams
   *
   * @expectedException P7\SSO\Exception\InvalidArgumentException
   */
  public function testConstructorArgException($missingParam) {

    $allParams = $this->validTokenArray;

    unset($allParams[$missingParam]);

    $tokens = new TokenSet($allParams);
  }

  public function testIsAccessTokenExpiredFalse() {
    $tokenParams = $this->validTokenArray;
    $tokenParams['expires_in'] = 62;//tokenset is set to expire 60s before access token actually expires

    $tokens = new TokenSet($tokenParams);
    $this->assertFalse($tokens->isAccessTokenExpired());
  }

  public function testIsAccessTokenExpiredTrue() {
    $tokenParams = $this->validTokenArray;
    $tokenParams['expires_in'] = 58;//tokenset is set to expire 60s before access token actually expires

    $tokens = new TokenSet($tokenParams);
    $this->assertTrue($tokens->isAccessTokenExpired());
  }
}
