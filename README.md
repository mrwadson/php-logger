# PHP Logger

Ease to use simple file logger written on php which implement all the log levels defined in the [RFC 5424 specification](https://tools.ietf.org/html/rfc5424):
**emergency**, **alert**, **critical**, **error**, **warning**, **notice**, **info** and **debug**.

## Requirements

- PHP >= 7.1

## Installation

Install through [composer](https://getcomposer.org/doc/00-intro.md):

```shell
composer install --no-dev # or without --no-dev flag if need the tests
composer update mrwadson/php-logger # or if already composer.lock file exists
```

To add as a VCS repository add following lines in your `composer.json` file:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mrwadson/php-logger.git"
        }
    ],
    "require": {
        "mrwadson/php-logger": "dev-master"
    }
}
```

## Usage

Just use the logger in your code like this:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use mrwadson\logger\Log;

// Setup log dir (default "log" dir)
// Log::options(['log_dir' => __DIR__ . '/log']);

// Log row
Log::log('Your logged message');

// Log array as debug in one row
Log::options(['log_array_in_one_row' => true]);
Log::debug([
    'Key first' => 'The first value of the array',
    'Key second' => 'The second value of the array'
]);

// Log error
try {
    throw new RuntimeException('This is a Runtime Exception');
} catch (Exception $e) {
    Log::error($e->getMessage()); // Or Log::log($e->getMessage(), Log::ERROR)
}
```

Will output in the log `log/log-2022-11-12.log` file:

```text
[2022-11-12 22:33:44]: INFO - Your logged message
[2022-11-12 22:33:44]: DEBUG - Array([Key first] => The first value of the array[Key second] => The second value of the array)
[2022-11-12 22:33:44]: ERROR - This is a Runtime Exception
```

Buffer captured example:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use mrwadson\logger\Log;

// Setup log dir
Log::options(['log_dir' => __DIR__ . '/log']);

Log::obStart();
echo 'This is a first record.' . PHP_EOL;
echo 'This is a second record.';
sleep(5);
Log::obEnd();
```

Will output in the log `log/log-2022-11-12.log` file:

```text
[2022-11-12 22:33:44]: INFO - 
This is a first record.
This is a second record.
```

Timer example:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use mrwadson\logger\Log;

// Setup log dir
Log::options(['log_dir' => __DIR__ . '/log', 'overwrite_log_file' => true]);

Log::timeStart();
sleep(3);
$time = Log::timeEnd();

Log::log('This page loaded in ' . $time . ' seconds', Log::DEBUG);
```

Will output in the log `log/log-2022-11-12.log` file:

```text
[2022-11-12 22:33:44]: DEBUG - This page loaded in 3.00 seconds
```

## Tests

Running the tests:

```shell
composer test
```
