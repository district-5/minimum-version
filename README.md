[![CI](https://github.com/district-5/php-mondoc/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/district-5/php-mondoc/actions)
[![Latest Stable Version](http://poser.pugx.org/district5/mondoc/v)](https://packagist.org/packages/district5/mondoc)
[![PHP Version Require](http://poser.pugx.org/district5/mondoc/require/php)](https://packagist.org/packages/district5/mondoc)
[![Codecov](https://codecov.io/gh/district-5/php-mondoc/branch/master/graph/badge.svg)](https://codecov.io/gh/district-5/php-mondoc)

# Minimum version enforcement for API services...

### Slim Framework 4 usage

See the official [Slim Framework 4](https://www.slimframework.com/docs/v4/objects/routing.html#route-middleware)
middleware documentation for more information.

There are two methods of constructing the middleware:

#### Option 1, using static values

```php
use District5\MinimumVersion\Slim\Slim4Middleware;

string $minimumVersionEnvKey, string|null $allowedVersionsEnvKey = null, string $headerName = self::VERSION_HEADER

$app->add(
    Slim4Middleware::fromValues(
        '1.0.0', // Minimum version to accept
        ['0.9.9', '1.0.0'], // Explicitly allowed versions
        'X-Api-Version' // Header name to check version against, defaults to 'X-Version'
    )
);
```

#### Option 2, using environment variables

```php
use District5\MinimumVersion\Slim\Slim4Middleware;
use District5\MinimumVersion\Slim\Slim4Middleware::VERSION_HEADER;

$app->add(
    Slim4Middleware::fromEnv(
        'MINIMUM_VERSION', // Environment variable name for minimum version, e.g. '1.0.0'
        'ALLOWED_VERSIONS', // Environment variable name for allowed versions, comma-separated, e.g. '0.0.9, 1.0.0'
        Slim4Middleware::VERSION_HEADER // This is the default value of 'X-Version'. The header name to check version
    )
);
```

### Install with composer...

```
composer require district5/minimum-version
```

### Testing...

```
composer install
./vendor/bin/phpunit
```
