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
composer require p7s1-ctf/7pass-php-sdk
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

Once you have the credentials available, you can go ahead and install the dependencies:

```
composer install
```

Next, you can go to the `public_html` directory and create the local
configuration file:

```
cd public_html
cp config.local.php.example config.local.php
```

Edit the `config.local.php` file in your favorite editor and fill out
the details. You should have all of the parameters at your disposal
after your client is set up. For testing, keep the environment set to
`qa`. Once that's done, you can start the application using the PHP's
built-in server:

```
php -S localhost:8000
```

The example application should be now available at
`http://localhost:8000`. The application will guide you through the
most common use cases of the library and show you code examples and
server responses along the way.

## API Usage

You strongly encouraged to go over the example application first. It
will show you the API calls with more comments and real values. It
will also show you the real responses from the 7Pass SSO service as
you progress.

To use the library, it's necessary to initialize it with the
credentials of the client we want to use. If you don't have the
credentials yet, please see above.

- client_id (required)
- client_secret (required)

If you're starting the development, it's always a good idea to work
against a non live instance of the 7Pass SSO service. To specify the
instance (the environment) against which you want to issue the
requests, you can pass an additional key called environment to the
configuration. There're currently two environments running: QA and
production. Don't forget to switch to the production version before
you release your application to the public.


```php
$config = [
  'client_id' => 'YOUR_CLIENT_ID',
  'client_secret' => 'YOUR_CLIENT_SECRET',
  'environment' => 'qa' // Optional, defaults to 'production'
];

// Creates the configuration object
$ssoConfig = new P7\SSO\Configuration($config);

// Pass the configuration to the SSO object
$sso = new P7\SSO($ssoConfig);
```

## Authentication flow

The authentication process is simple and the high level view is as
follows: The user is redirected to the 7Pass SSO service using a
specially crafted URL and he/she signs in (or signs up). Once the user
has finished the process, he/she is redirected back to your
application with a special code in the URL. The application will then
use the code in order to get the user's details. The process may vary
depending on the passed options.

### 1. Get a redirect URL

The library automatically handles the generation of the URL to which
the user needs to be redirected. The only required parameter is the
`redirect_uri` URL. The URL needs to be absolute and can be arbitrary
(given that it is registered to the client) but will by convention
lead to the same host and a route called "callback".

```php
$options = [
  'redirect_uri' => 'https://example.com/callback', // Required.
  'scope' => 'openid profile email', // Optional, default value.
  'response_type' => 'code' // Optional, default value.
];

$redirectUrl = $sso->authorization()->authorizeUri($options);
```

The library will automatically set the `client_id` and `nonce` (a
unique request identifier) parameters automatically.

### 2. Redirect an user

Now it's time to redirect the user to the generated URL. In plain PHP,
you can set the `Location` header:

```php
header('Location: ' . $redirectUrl);
exit;
```

### 3. Handling 7Pass callback

After the user has finished with the sign in/up dialog, he/she has
been redirected to the redirect_uri URL with the outcome of the sign
in process. The user might have successfully authenticated but also
might have decided to cancel the process or some other error might
have happened. Therefore it's important have proper error handling.

Whenever an error occurs, there will be two query parameters present
in the URL - error and `error_description`. The error parameter
contains an error code and the error_description contains a human
readable description of the error. Handle the error and display
appropriate message to your end-user.

```php
if(!empty($_GET['error'])) {
  $error = $_GET['error'];
  $errorDescription = $_GET['error_description'];

  // Handle the error and display appropriate message to your end-user.
}
```

In case there's been no error, there will be a code parameter in the
URL. This code along with the redirect_uri can be used to retrieve the
tokens which will later allow you to fetch the actual information
about the user. These tokens are specific to the particular user and
are private. You need to keep them secured and do not share them with
anybody.

```php
$tokens = $sso->authorization()->callback([
    'code': $_GET['code']
]);
```

The received response will have the following structure. Run the
example application to see the real values.

```php
P7\SSO\TokenSet(
    [access_token] => ACCESS_TOKEN
    [token_type] => 'Bearer'
    [refresh_token] => REFRESH_TOKEN
    [expires_in] => 7200
    [id_token] => JWT_STRING
    [id_token_decoded] => DECODED_JWT,
    [received_at] => RECEIVED_AT_TIMESTAMP
)
```

Note: The `id_token_decoded` value is decoded from the `id_token`
field and verified. If token verification fails, the
`P7\SSO\Exception\TokenVerificationException` exception is thrown.

Further, the call might throw `P7\SSO\Exception\ApiException` in
case the `code` has already been used or is otherwise invalid.

### 4. Caching an access token

The tokens are represented in a single object of type
`P7\SSO\TokenSet`. You can serialize the object directly, however, we
recommend that you first convert the `TokenSet` into a simpler array
object and serialize it instead. As soon as you need the `TokenSet`
again, you can pass the array object into its constructor.

```php
// Serialize the TokenSet object and store it in e.g. the current session
$_SESSION['tokens'] = $tokens->getArrayCopy();

// Deserialize and get the TokenSet object again
$tokens = new \P7\SSO\TokenSet($_SESSION['tokens']);
```

Access tokens are valid for certain amount of time specified in
seconds in the `expires_in` field. Once the access token expires, it
can no longer be used. You can obtain a new one using the refresh
token as follows:

```php
if($tokens->isAccessTokenExpired()) {
  $tokens = $sso->authorization()->refresh([
    'refresh_token' => $tokens->refresh_token
  ]);
}
```

_Note: `refresh()` method above also accepts `P7\SSO\TokenSet` object as an argument._

### 5. Calling our API endpoints

Now that we're sure the tokens are up to date, we can start making
requests to the 7Pass SSO service to get the user data.

Same as with the previous example, run the example application to see
the real server response.

```php
$accountClient = $sso->accountClient($tokens);
$response = $accountClient->get('me');
```

The 7Pass SSO service offers quite a few of these endpoints. To learn
more about them, you can go to
[the official documentation's overview](http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-Accounts).

## Backoffice requests

The library can also be used to perform "backoffice" requests. These
requests are initiated without direct involvement of users and are
meant for administrative purposes. Your client needs to have the
`backoffice_code` grant type allowed. You also need to know the ID of
the user you want to work with.

Backoffice requests are used to make an API calls on behalf of other users. To get access token for these requests
you need to use special grant type 'backoffice_code' providing an account_id.
Upon successful authentication you get a same token set as using standard flow described above.

```php
$config = [
  'environment' => 'qa' // Optional, defaults to 'production',
  'service_id' => 'SERVICE_ID', // Required for backoffice access
  'backoffice_key' => 'BACKOFFICEC_KEY' // Required for backoffice access
];

// Creates the configuration object
$ssoConfig = new P7\SSO\Configuration($config);

// Pass the configuration to the SSO object
$sso = new P7\SSO($ssoConfig);

// Get the tokens using the backoffice
$tokens = $sso->authorization()->backoffice([
  'account_id' => 'account_id' // Required, the ID of the user you want to access
]);

// Use the client as you normally would when using the standard access
$accountClient = $sso->accountClient($tokens);
$response = $accountClient->get('me');
```

The response will be as usual. Once you get the tokens, the 7Pass SSO
service will act as if the access token has been obtained using the
"standard" way.

## Caching

Before the library can work properly, it needs to fetch some
information from the configured instance of the 7Pass SSO service. To
make sure these information are not downloaded every time, the library
uses a configurable caching mechanism.

Under the hood, it uses the
[Stash Caching Library](https://github.com/tedious/stash/) library and
tries to use the [Apc](http://www.stashphp.com/Drivers.html#apc)
driver first. If not avaible, it will use the
[Filesystem driver](http://www.stashphp.com/Drivers.html#filesystem)
instead.

If desired, you can configure your own cache driver as follows:

```php
$ssoConfig = new P7\SSO\Configuration($config);

$driver = new Stash\Driver\Memcache();
$driver->setOptions(array('servers' => array('127.0.0.1', '11211')));

$ssoConfig->setCachePool(new Stash\Pool($driver));

$sso = new P7\SSO($ssoConfig);
```

To manually refresh the cache (not needed under normal circumstances),
use the `rediscover` method:

```php
$sso->getConfig()->rediscover();
```

If you have any questions or something's not working as expected,
please do not hesitate to contact Filip Skokan at
filip.skokan@prosiebensat1.com.

## Running the tests

The library uses PHPUnit for testing. The recommended version is
**4.8** although the tests may run successfully on an older version of
the 4 series as well.

```
composer install
phpunit
```
