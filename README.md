Apache2 access and error logs parser
====================================

[![Build Status](https://travis-ci.org/mvar/apache2-log-parser.png?branch=master)](https://travis-ci.org/mvar/apache2-log-parser)

Installation
---

Add package to your `composer.json`:

```json
{
    "require": {
        "mvar/apache2-log-parser": "dev-master"
    }
}
```

And run Composer to update your packages:

```bash
php composer.phar update
```

Usage
-----

### Parsing single Apache2 access log line

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use MVar\Apache2LogParser\AccessLogParser;

// Format can be any of predefined `AccessLogParser::FORMAT_*` constants or custom string
$parser = new AccessLogParser(AccessLogParser::FORMAT_COMBINED);

// String which you want to parse
$line = '66.249.78.230 - - [29/Dec/2013:16:07:58 +0200] "GET /my-page/ HTTP/1.1" 200 2490 "-" ' .
    '"Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)"';

var_export($parser->parseLine($line));
```

The above example will output:

```php
array (
  'remote_host' => '66.249.78.230',
  'identity' => '-',
  'remote_user' => '-',
  'time' => '2013-12-29T16:07:58+0200',
  'request_line' => 'GET /my-page/ HTTP/1.1',
  'response_code' => '200',
  'bytes_sent' => '2490',
  'request' =>
  array (
    'method' => 'GET',
    'path' => '/my-page/',
    'protocol' => 'HTTP/1.1',
  ),
  'request_headers' =>
  array (
    'referer' => '-',
    'user_agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
  ),
)
```

### Iterate through Apache log file

Log iterator reads log file line by line. This means that it is possible to
parse huge files with low memory usage.

Let's say we have Apache log file `access.log` with following content:

```
192.168.25.1 - - [25/Jun/2012:14:26:05 -0700] "GET /favicon.ico HTTP/1.1" 404 498
192.168.25.1 - - [25/Jun/2012:14:26:05 -0700] "GET /icons/blank.gif HTTP/1.1" 200 438
```

To parse whole log file line by line it needs only to create new iterator with
file name and parser arguments:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use MVar\Apache2LogParser\AccessLogParser;
use MVar\Apache2LogParser\LogIterator;

$parser = new AccessLogParser(AccessLogParser::FORMAT_COMMON);

foreach (new LogIterator('access.log', $parser) as $line => $data) {
    printf("%s %s\n", $data['request']['method'], $data['request']['path']);
}
```

The above example will output:

```
GET /favicon.ico
GET /icons/blank.gif
```
