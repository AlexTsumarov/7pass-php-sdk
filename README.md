# 7pass PHP SDK

## Installation

TODO

```
composer install 7pass-php-sdk
```

##Usage


```
$sso = new SSO(new SSO\Configuration($config));
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
(
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
    'refresh_token' => $refreshToken
])
```

`$tokens` contains same token values as `$sso->authorization()->callback()` returns.


### 5. Calling our API endpoints



```
$api->get('/me')
```


##Calling our API

Now when


## Running Example

    $ cd public_html
    $ php -S localhost:8000

Sample page should be now available at http://localhost:8000.
