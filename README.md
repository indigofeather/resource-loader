# Resource-Loader

The resource loader for json, ini, yaml, php

## Requirement

 - PHP >=5.4

## Installing via Composer

The recommended way to install Resource-Loader is through Composer.

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, update your project's composer.json file to include Resource-Loader:

```json
{
    "require": {
        "indigofeather/resource-loader": "dev-master"
    }
}
```

## Usage

```php
<?php

require_once 'vendor/autoload.php';

use Indigofeather\ResourceLoader\Container;

$resource = new Container();
$data = $resource->addPaths([__DIR__.'/resources/foo', __DIR__.'/resources/bar'])
    ->setDefaultFormat('yml')
    ->load('data');

print_r($data);
```

## License

MIT