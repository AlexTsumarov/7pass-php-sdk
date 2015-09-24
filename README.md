# 7pass PHP SDK

## Installation

```
composer install 7pass-php-sdk
```

__Minimum requirements__

- php: >=5.5.0


## Running an example app

```
cd public_html
php -S localhost:8000
```

Sample page should be now available at http://localhost:8000.


##Usage

```
$sso = new P7\SSO(new P7\SSO\Configuration($config));
```

Configuration options:

- client_id (required)
- client_secret (required)
- environment - default: production; Available options: 'production' (https://sso.7pass.de), 'staging' (https://sso.qa.7pass.ctf.prosiebensat1.com)
- redirect_uri -

If you are authorized to backoffice requests you can also configure:

- service_id (required)
- backoffice_key (required)


##Authentication flow


### 1. Get a redirect URL

```
$redirectUrl = $sso->authorization()->uri($options);
```

Options:

- redirect_uri - required (if not specified in SSO configuration)
- scope - default: 'openid profile email'
- response_type - default: 'code'

_Note: client_id and nonce parameters are set automatically._


### 2. Redirect an user

Redirect an user to generated URL `$redirectUrl`


### 3. Handling 7pass SSO callback

User authenticates successfully, they reject an authentication, or they might be an unexpected server error.

In case of an error, error type and description is sent as 'error' and 'error_description' parameter which can be used to render appropriate error message to the user.

In order to get an access token, your server should call 7pass token endpoint - this can be achieved using `$sso->authorization()->callback()` method.

```
$queryParams = $_GET;

if(!empty($queryParams['error'])) {
    //TODO handle error state
    return;
}

$tokens = $sso->authorization()->callback([
    'code': $queryParams['code']
]);
```

Example response:
```
stdClass(
    [access_token] => ACCESS_TOKEN
    [token_type] => 'Bearer'
    [refresh_token] => REFRESH_TOKEN
    [expires_in] => 7200
    [id_token] => JWT_STRING
    [id_token_decoded] => DECODED_JWT
)
```

_Note: `id_token_decoded` is decoded and verified OpenID Connect JWT. If token verification fails `P7\SSO\Exception\TokenVerificationException` is thrown_

Exceptions:

- BadRequestException - invalid OAuth request
- TokenValidationException - id_token is not valid


### 4. Caching an access token

Access token is valid for certain amount of time specified in seconds in `expires_in`.
You should cache and reuse this access token for this period on every request. Once token is near to expire you can use `refresh_token`
to obtain new access with extended expiration time without an user's consent.

Using a refresh token:

```
$tokens = $sso->authorization()->refresh([
    'refresh_token' => $tokens->refresh_token
]);
```

`$tokens` contains same token set as `$sso->authorization()->callback()` returns.


### 5. Calling our API endpoints



```
$apiClient = $sso->api($tokens->access_token);
$response = $apiClient->get('/me');
```


## Backoffice requests

Backoffice requests are used to make an API calls on behalf of other users. To get access token for these requests
you need to use special grant type 'backoffice_code' providing an account_id.
Upon successful authentication you get a same token set as using standard flow described above.

```
$tokens = $sso->authorization()->backoffice([
    'account_id' => $accountId
]);

$apiClient = $sso->api($tokens->access_token);
$response = $apiClient->get('/me');
```


## Caching

SDK caches OpenID configuration and public keys. SDK make use of [Stash Caching Library](https://github.com/tedious/stash/).
By default it tries to use [Apc](http://www.stashphp.com/Drivers.html#apc) if available,
otherwise it uses [Filesystem driver](http://www.stashphp.com/Drivers.html#filesystem) with default settings.

You can set and configure your own cache storage as below:

```
$ssoConfig = new P7\SSO\Configuration($config);

$driver = new Stash\Driver\Memcache();
$driver->setOptions(array('servers' => array('127.0.0.1', '11211')));

$ssoConfig->setCachePool(new Stash\Pool($driver));

$sso = new P7\SSO($ssoConfig);
```


Use `rediscover()` to update cached OpenID Configuration.

```
$sso->getConfig()->rediscover();

$ssc->getConfig()->getOpenIdConfig(); //returns updated config now
```