# 7pass-php-sdk
7pass PHP SDK

## Installation

```
composer install
```

Create/edit Authorization Client under one of your services in [7pass Admin](http://admin.7pass.dev/services/) and set the following:

 - URIs > Redirect URIs: add `http://localhost:8000/callback`
 - OpenID > Token grant types: add `backoffice_code`

Copy `public_html/config.local.php.example` to `public_html/config.local.php` and edit according your settings.


## Running Example

    $ cd public_html
    $ php -S localhost:8000

Sample page should be now available at http://localhost:8000.
