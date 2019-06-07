# ShopenGroup/SatisHook

[![Build Status](https://travis-ci.org/shopen-group/satis-hook.svg?branch=develop)](https://travis-ci.org/shopen-group/satis-hook)


Simple app for rebuilding [composer/satis](https://github.com/composer/satis) via webhook.
* No database required

## Install

### Install composer package
```bash
composer require shopen-group/satis-hook dev-develop
```

### Create config
```yaml
# config.yaml
secret:
  enabled: true # secret token is enabled
  location: param # location of "secret" parameter (param|header)
  value: veslo # secret value
  name: key # secret parameter name

satis:
  php: /usr/bin/php
  bin: ../satis/bin/satis 
  config: ../satis.json 
  output: ../web
```

### Create entrypoint

Our entrypoint will be `hook.php` which is publicly accessible via HTTP.

ApplicationFactory::createApplication has three parameters. You can customise the first two of them.
* path to config.yaml
* path to __writable__ TEMP folder (for storing requests)

```php
<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ShopenGroup\SatisHook\ApplicationFactory;

$application = ApplicationFactory::createApplication(__DIR__ . '/config.yaml', __DIR__ . '/temp', __DIR__ . '/logs', $argc);
$application->run();
```

## Usage

### Webhook
The app is accepting GET and POST requests through `https://127.0.0.1/hook.php`.
There is an optional parameter "build-all". If the "build-all" parameter is present in request you can build all packages not necessarily just one particular package (`http:/127.0.0.1/hook.php?repository=package/name`).

### Queue process
There is a second "layer" of application - queue processing.
To start processing accepted requests you have to run command below via CLI.
```bash
php hook.php satis-hook:build
```

We use [supervisord](http://supervisord.org) to keep queue processing up.