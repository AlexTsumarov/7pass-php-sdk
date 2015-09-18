<?php
return [
  'sso_client' => [
    'client_id' => '54523ed2d3d7a3b4333a9426',
    'service_id' => '5411719c656332687d000000',
    'client_secret' => 'd7078d0b804522d6c28677d826e39879122c7a80214cc9bfa60be6022f503fec',
    'environment' => 'development',
    'backoffice_key' => openssl_pkey_get_private('file://' . __DIR__ . '/../tests/fixtures/certs/rsa.pem')
  ],
  'account_id' => '55e6c6f92ae3fdfb7467209c'
];