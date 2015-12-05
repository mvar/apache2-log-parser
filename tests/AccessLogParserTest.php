<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

/**
 * @covers \MVar\Apache2LogParser\AccessLogParser
 */
class AccessLogParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for parseLine().
     *
     * @param string $format
     * @param string $logLine
     * @param array  $expectedResult
     *
     * @dataProvider getTestParseLineData()
     */
    public function testParseLine($format, $logLine, $expectedResult)
    {
        $parser = new AccessLogParser($format);
        $result = $parser->parseLine($logLine);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test for parseLine() if pattern is cached.
     */
    public function testParseLineCachedPattern()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AccessLogParser $parser */
        $parser = $this->getMock(
            '\\MVar\\Apache2LogParser\\AccessLogParser',
            ['getCallbackPatterns'],
            ['%%']
        );

        $parser->expects($this->once())->method('getCallbackPatterns');

        $parser->parseLine('%');

        // Pattern should be cached
        $parser->parseLine('%');
    }

    /**
     * Test for parseLine() in case of invalid line.
     *
     * @param string $format
     * @param string $logLine
     *
     * @dataProvider getTestParseLineNoMatchesData()
     * @expectedException \MVar\Apache2LogParser\Exception\NoMatchesException
     * @expectedExceptionMessage line does not match
     */
    public function testParseLineNoMatches($format, $logLine)
    {
        $parser = new AccessLogParser($format);
        $parser->parseLine($logLine);
    }

    /**
     * Data provider for testParseLine().
     *
     * @return array[]
     */
    public function getTestParseLineData()
    {
        return [
            [
                AccessLogParser::FORMAT_COMMON,
                '127.0.0.1 - frank [10/Oct/2000:13:55:36 -0700] "GET /apache_pb.gif HTTP/1.0" 200 2326',
                [
                    'remote_host' => '127.0.0.1',
                    'identity' => '-',
                    'remote_user' => 'frank',
                    'time' => '10/Oct/2000:13:55:36 -0700',
                    'request_line' => 'GET /apache_pb.gif HTTP/1.0',
                    'request' => [
                        'method' => 'GET',
                        'path' => '/apache_pb.gif',
                        'protocol' => 'HTTP/1.0'
                    ],
                    'response_code' => '200',
                    'bytes_sent' => '2326',
                ],
            ],
            [
                AccessLogParser::FORMAT_COMBINED,
                '127.0.0.1 - frank [10/Oct/2000:13:55:36 -0700] "GET /apache_pb.gif HTTP/1.0" 200 2326 ' .
                    '"http://www.example.com/start.html" "Mozilla/4.08 [en] (Win98; I ;Nav)"',
                [
                    'remote_host' => '127.0.0.1',
                    'identity' => '-',
                    'remote_user' => 'frank',
                    'time' => '10/Oct/2000:13:55:36 -0700',
                    'request_line' => 'GET /apache_pb.gif HTTP/1.0',
                    'request' => [
                        'method' => 'GET',
                        'path' => '/apache_pb.gif',
                        'protocol' => 'HTTP/1.0',
                    ],
                    'response_code' => '200',
                    'bytes_sent' => '2326',
                    'request_headers' => [
                        'Referer' => 'http://www.example.com/start.html',
                        'User-Agent' => 'Mozilla/4.08 [en] (Win98; I ;Nav)',
                    ],
                ],
            ],
            [
                AccessLogParser::FORMAT_COMBINED,
                '127.0.0.1 - - [28/Dec/2013:19:03:49 +0200] "GET /test-page/ HTTP/1.1" ' .
                    '200 8359 "-" "Symfony2 BrowserKit"',
                [
                    'remote_host' => '127.0.0.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '28/Dec/2013:19:03:49 +0200',
                    'request_line' => 'GET /test-page/ HTTP/1.1',
                    'request' => [
                        'method' => 'GET',
                        'path' => '/test-page/',
                        'protocol' => 'HTTP/1.1',
                    ],
                    'response_code' => '200',
                    'bytes_sent' => '8359',
                    'request_headers' => [
                        'Referer' => '-',
                        'User-Agent' => 'Symfony2 BrowserKit',
                    ],
                ],
            ],
            [
                AccessLogParser::FORMAT_COMBINED,
                '66.249.78.230 - - [29/Dec/2013:16:07:58 +0200] "GET /robots.txt HTTP/1.1" ' .
                    '200 408 "-" "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)"',
                [
                    'remote_host' => '66.249.78.230',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '29/Dec/2013:16:07:58 +0200',
                    'request_line' => 'GET /robots.txt HTTP/1.1',
                    'request' => [
                        'method' => 'GET',
                        'path' => '/robots.txt',
                        'protocol' => 'HTTP/1.1',
                    ],
                    'response_code' => '200',
                    'bytes_sent' => '408',
                    'request_headers' => [
                        'Referer' => '-',
                        'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                    ],
                ],
            ],
            [
                AccessLogParser::FORMAT_COMBINED,
                '71.82.1.1 - - [29/Dec/2013:17:37:40 +0200] "GET / HTTP/1.1" 200 2577 "http://example.com/test/" ' .
                    '"Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko"',
                [
                    'remote_host' => '71.82.1.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '29/Dec/2013:17:37:40 +0200',
                    'request_line' => 'GET / HTTP/1.1',
                    'request' => [
                        'method' => 'GET',
                        'path' => '/',
                        'protocol' => 'HTTP/1.1',
                    ],
                    'response_code' => '200',
                    'bytes_sent' => '2577',
                    'request_headers' => [
                        'Referer' => 'http://example.com/test/',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
                    ],
                ],
            ],
            [
                AccessLogParser::FORMAT_COMBINED,
                // HTTP error 408 "Request Timeout"
                '192.168.25.1 - - [25/Jun/2012:14:00:14 -0700] "-" 408 0 "-" "-"',
                [
                    'remote_host' => '192.168.25.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '25/Jun/2012:14:00:14 -0700',
                    'request_line' => '-',
                    'response_code' => '408',
                    'request_headers' => [
                        'Referer' => '-',
                        'User-Agent' => '-',
                    ],
                ]
            ],
            [
                AccessLogParser::FORMAT_COMBINED,
                // HTTP error 414 "Request URI too long"
                '192.168.139.1 - - [10/Oct/2013:00:26:32 +0300] "GET /' . str_repeat('a', 7995) . '" 414 540 "-" "-"',
                [
                    'remote_host' => '192.168.139.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '10/Oct/2013:00:26:32 +0300',
                    'request_line' => 'GET /' . str_repeat('a', 7995),
                    'request' => [
                        'method' => 'GET',
                        'path' => '/' . str_repeat('a', 7995),
                    ],
                    'response_code' => '414',
                    'bytes_sent' => '540',
                    'request_headers' => [
                        'Referer' => '-',
                        'User-Agent' => '-',
                    ],
                ]
            ],
            [
                AccessLogParser::FORMAT_COMBINED,
                // When no bytes were sent
                '74.86.158.106 - - [09/Jan/2014:04:11:40 -0800] "HEAD / HTTP/1.1" 200 - "-" ' .
                    '"Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)"',
                [
                    'remote_host' => '74.86.158.106',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '09/Jan/2014:04:11:40 -0800',
                    'request_line' => 'HEAD / HTTP/1.1',
                    'request' => [
                        'method' => 'HEAD',
                        'path' => '/',
                        'protocol' => 'HTTP/1.1',
                    ],
                    'response_code' => '200',
                    'bytes_sent' => '-',
                    'request_headers' => [
                        'Referer' => '-',
                        'User-Agent' => 'Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)',
                    ],
                ],
            ],
            [
                AccessLogParser::FORMAT_COMBINED,
                '12.34.56.78 - - [06/Feb/2014:02:00:18 -0800] "GET / HTTP/1.1" 200 2151 "" "-"',
                [
                    'remote_host' => '12.34.56.78',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '06/Feb/2014:02:00:18 -0800',
                    'request_line' => 'GET / HTTP/1.1',
                    'request' => [
                        'method' => 'GET',
                        'path' => '/',
                        'protocol' => 'HTTP/1.1',
                    ],
                    'response_code' => '200',
                    'bytes_sent' => '2151',
                    'request_headers' => [
                        'User-Agent' => '-',
                    ],
                ],
            ],
            [
                AccessLogParser::FORMAT_COMBINED,
                '12.34.56.78 - - [20/May/2014:17:21:57 -0700] "GET /test/ HTTP/1.1" 200 1680 ' .
                    '"http://search.yahoo.com/search?p=what color" "Mozilla/5.0"',
                [
                    'remote_host' => '12.34.56.78',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '20/May/2014:17:21:57 -0700',
                    'request_line' => 'GET /test/ HTTP/1.1',
                    'request' => [
                        'method' => 'GET',
                        'path' => '/test/',
                        'protocol' => 'HTTP/1.1',
                    ],
                    'response_code' => '200',
                    'bytes_sent' => '1680',
                    'request_headers' => [
                        'User-Agent' => 'Mozilla/5.0',
                        'Referer' => 'http://search.yahoo.com/search?p=what color',
                    ],
                ],
            ],
            [
                AccessLogParser::FORMAT_VHOST_COMBINED,
                '127.0.1.1:80 127.0.0.1 - - [26/Jun/2012:10:41:10 -0700] "OPTIONS * HTTP/1.0" 200 126 "-" ' .
                    '"Apache/2.2.22 (Ubuntu) (internal dummy connection)"',
                [
                    'canonical_server_name' => '127.0.1.1',
                    'server_port' => '80',
                    'remote_host' => '127.0.0.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '26/Jun/2012:10:41:10 -0700',
                    'request_line' => 'OPTIONS * HTTP/1.0',
                    'request' => [
                        'method' => 'OPTIONS',
                        'path' => '*',
                        'protocol' => 'HTTP/1.0',
                    ],
                    'response_code' => '200',
                    'bytes_sent' => '126',
                    'request_headers' => [
                        'Referer' => '-',
                        'User-Agent' => 'Apache/2.2.22 (Ubuntu) (internal dummy connection)',
                    ],
                ],
            ],
            [
                // Test for percent sign
                '%% test',
                '% test',
                [],
            ],
            [
                // Test for full port support
                '%{canonical}p %{local}p %{remote}p test',
                '123 456 789 test',
                [
                    'canonical_port' => 123,
                    'local_port' => 456,
                    'remote_port' => 789,
                ],
            ],
            [
                // Test for status code of the original request
                '%s',
                '201',
                [
                    'original_status_code' => 201,
                ],
            ],
            [
                // Test for Size of response in bytes, excluding HTTP headers. In CLF format
                '%b',
                '-',
                [
                    'response_body_size' => 0,
                ],
            ],
            [
                // Test for predefined User-Agent format
                AccessLogParser::FORMAT_AGENT,
                'Symfony2 BrowserKit',
                [
                    'request_headers' => [
                        'User-Agent' => 'Symfony2 BrowserKit',
                    ],
                ],
            ],
            [
                // Test for request method
                '%m',
                'POST',
                [
                    'request_method' => 'POST',
                ],
            ],
            [
                // Test for request serve time
                '%D %T',
                '123 456',
                [
                    'request_time_us' => '123',
                    'request_time_s' => '456',
                ],
            ],
            [
                // Test for URL path
                '%U',
                '/path',
                [
                    'request_path' => '/path',
                ],
            ],
            [
                // Test for URL path and query string
                '%U%q',
                '/path?googleguy=googley',
                [
                    'request_path' => '/path',
                    'query_string' => '?googleguy=googley',
                ],
            ],
            [
                // Test for bytes received and transferred
                '%I %S',
                '123 456',
                [
                    'bytes_received' => '123',
                    'bytes_transferred' => '456',
                ],
            ],
            [
                // Test for predefined referer log format
                AccessLogParser::FORMAT_REFERER,
                'http://www.example.com/ -> /my-page/',
                [
                    'request_headers' => [
                        'Referer' => 'http://www.example.com/',
                    ],
                    'request_path' => '/my-page/',
                ],
            ],
            [
                // Test for request protocol
                '%H',
                'HTTP/1.0',
                [
                    'request_protocol' => 'HTTP/1.0',
                ],
            ],
            [
                // Test for local IP address
                '%A',
                '192.168.5.128',
                [
                    'local_ip' => '192.168.5.128',
                ],
            ],
            [
                // Test for client IP address
                '%a',
                '192.168.5.1',
                [
                    'client_ip' => '192.168.5.1',
                ],
            ],
            [
                // Test for real client IP address (e.g. when proxy is used)
                '%{c}a',
                '192.168.5.55',
                [
                    'peer_ip' => '192.168.5.55',
                ],
            ],
            [
                // Test for cookies
                '"%{cookie1}C" "%{cookie_2}C"',
                '"cookie1 contents" "cookie_2 contents"',
                [
                    'cookies' => [
                        'cookie1' => 'cookie1 contents',
                        'cookie_2' => 'cookie_2 contents',
                    ],
                ],
            ],
            [
                // Test for filename
                '"%f"',
                '"/home/user/public_html/favicon.ico"',
                [
                    'filename' => '/home/user/public_html/favicon.ico',
                ],
            ],
            [
                // Test for connection status
                '%X',
                '+',
                [
                    'connection_status' => '+',
                ],
            ],
            [
                // Test for environment variables
                '"%{CUSTOM_VARIABLE}e"',
                '"custom variable contents"',
                [
                    'env_vars' => [
                        'CUSTOM_VARIABLE' => 'custom variable contents',
                    ],
                ],
            ],
            [
                // Test for response headers
                '%{Content-Length}o',
                '1553',
                [
                    'response_headers' => [
                        'Content-Length' => '1553',
                    ],
                ],
            ],
            [
                // Test for response headers
                '%{Content-Length}o "%{Content-Encoding}o"',
                '1553 "gzip"',
                [
                    'response_headers' => [
                        'Content-Length' => '1553',
                        'Content-Encoding' => 'gzip',
                    ],
                ],
            ],
            [
                // Test for module notes
                '%{outstream}n/%{instream}n (%{ratio}n%%)',
                '512/1024 (50%)',
                [
                    'mod_vars' => [
                        'outstream' => '512',
                        'instream' => '1024',
                        'ratio' => '50',
                    ],
                ],
            ],
            [
                // Test for the server name
                '%V',
                'www.domain.tld',
                [
                    'server_name' => 'www.domain.tld',
                ],
            ],
            [
                // Test for number of keep-alive requests handled on this connection
                '%k',
                '2',
                [
                    'keepalive_requests' => '2',
                ],
            ],
            [
                // Test for process and thread ID
                '%P %{pid}P %{tid}P %{hextid}P',
                '229 12 34 56',
                [
                    'process_id' => '229',
                    'pid' => '12',
                    'tid' => '34',
                    'hextid' => '56',
                ],
            ],
            [
                // Test for the request log ID
                '%L',
                '55',
                [
                    'log_id' => '55',
                ],
            ],
            [
                // Test for the handler generating the response
                '%R',
                'application/x-httpd-php',
                [
                    'response_handler' => 'application/x-httpd-php',
                ],
            ],
            // Check if format string quoting works correctly
            ['%z/%z', '%z/%z', []],
            ['{%{test}z}', '{%{test}z}', []],
            ['{%{test}z}*', '{%{test}z}*', []],
            ['{%{test}z}/%z', '{%{test}z}/%z', []],
            ['{%{test}z}/%z-%>z', '{%{test}z}/%z-%>z', []],
            ['{%{test}z}/%z-(%>z)', '{%{test}z}/%z-(%>z)', []],
            // Also do not quote directives with modifiers
            ['%>{test}z', '%>{test}z', []],
            ['%<{test}z', '%<{test}z', []],
            ['%200{test}z', '%200{test}z', []],
            ['%!200{test}z', '%!200{test}z', []],
            ['%200,201{test}z', '%200,201{test}z', []],
            ['%!200,201{test}z', '%!200,201{test}z', []],
        ];
    }

    /**
     * Data provider for testParseLineNoMatches().
     *
     * @return array[]
     */
    public function getTestParseLineNoMatchesData()
    {
        return [
            ['%b', 'abc'],
            ['%B', '-'],
        ];
    }
}
