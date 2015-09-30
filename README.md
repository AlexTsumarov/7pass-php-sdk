# 7Pass PHP SDK

7Pass PHP SDK is a PHP library for interacting with the
[7Pass SSO service](https://7pass.de). You can use this library to
implement authentication for your website and take advantage of the
already existing features that 7Pass SSO offers.

## Installation

Before you start working with the library, you need to make sure your
system meets the required criteria. As of now, the library requires
PHP 5.5.0 and newer with cURL support.

The library is distributed as a composer package. If you haven't
already, please install [Composer](https://getcomposer.org) using
[the official instructions](https://getcomposer.org/doc/00-intro.md#system-requirements). Once
Composer is installed, you can install the library as follows:

```
php composer.phar require p7s1/7pass-php-sdk
```

This will automatically add the library to the list of your
application's dependencies.

## Running the example application

To demonstrate the functionality of the library, there's an
interactive tutorial / guide available. Before we get started setting
it up, you need your web application's client. The client represents
the entity which is associated with a service you want to authenticate
to.

To obtain the client credentials, you first need to contact the 7Pass
SSO team or contact Filip Skokan directly at
filip.skokan@prosiebensat1.com.

Once you have the credentials available, you can go again and install the dependencies:

```bash
php composer.phar install
```

Next, you can go to the `public_html` directory and create the local
configuration file:

```bash
cd public_html
cp config.local.php.example config.local.php
```

Edit the `config.local.php` file in your favorite editor and fill out
the details. Once that's done, you can start the application using the
PHP's built-in server:

```bash
php -S localhost:8000
```

The example application should be now available at
`http://localhost:8000`. The application will guide you through the
most common use cases of the library and show you code examples and
server responses along the way.

## API Usage

```
$sso = new P7\SSO(new P7\SSO\Configuration($config));
```

Configuration options:

- client_id (required)
- client_secret (required)
- environment - default: production; Available options: 'production' (https://sso.7pass.de), 'staging' (https://sso.qa.7pass.ctf.prosiebensat1.com)

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

Redirect an user to generated 7Pass server `$redirectUrl` server where they authenticate.


### 3. Handling 7Pass callback

An user is redirect back to your server now. They either authenticated successfully, cancelled an authentication, or there might be an unexpected server error.

In case of an error, error type and description is sent as 'error' and 'error_description' query parameters which can be used to render appropriate error message to the user.

In order to get an access token, your server should call 7Pass token endpoint - this can be achieved using `$sso->authorization()->callback()` method as shown below.

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
to obtain new access with extended expiration time even without user's consent.

Using a refresh token:

```
$tokens = $sso->authorization()->refresh([
    'refresh_token' => $tokens->refresh_token
]);
```

`$tokens` contains same token set as `$sso->authorization()->callback()` returns.


### 5. Calling our API endpoints



```
$accountClient = $sso->accountClient($tokens->access_token);
$response = $accountClient->get('/me');
```


## Backoffice requests

Backoffice requests are used to make an API calls on behalf of other users. To get access token for these requests
you need to use special grant type 'backoffice_code' providing an account_id.
Upon successful authentication you get a same token set as using standard flow described above.

```
$tokens = $sso->authorization()->backoffice([
    'account_id' => $accountId
]);

$backofficeClient = $sso->backofficeClient($tokens->access_token);
$response = $backofficeClient->get('/me');
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
```
