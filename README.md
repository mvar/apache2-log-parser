Apache2 access and error logs parser
====================================

[![Build Status](https://secure.travis-ci.org/mvar/apache2-log-parser.png?branch=master)](http://travis-ci.org/mvar/apache2-log-parser)

Installation
---


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
  'request_method' => 'GET',
  'request_path' => '/my-page/',
  'request_protocol' => 'HTTP/1.1',
  'response_code' => '200',
  'bytes_sent' => '2490',
  'referer' => '-',
  'user_agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
)
```
