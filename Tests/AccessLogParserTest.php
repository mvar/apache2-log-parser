<?php
/**
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser\Tests;

use MVar\Apache2LogParser\AccessLogParser;

class AccessLogParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for parseLine()
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
     * Test for parseLine() in case of invalid line
     *
     * @param string $format
     * @param string $logLine
     *
     * @dataProvider getTestParseLineNoMatchesData()
     * @expectedException \MVar\Apache2LogParser\NoMatchesException
     * @expectedExceptionMessage line does not match
     */
    public function testParseLineNoMatches($format, $logLine)
    {
        $parser = new AccessLogParser($format);
        $parser->parseLine($logLine);
    }

    /**
     * Data provider for testParseLine()
     *
     * @return array[]
     */
    public function getTestParseLineData()
    {
        return array(
            array(
                AccessLogParser::FORMAT_COMMON,
                '127.0.0.1 - frank [10/Oct/2000:13:55:36 -0700] "GET /apache_pb.gif HTTP/1.0" 200 2326',
                array(
                    'remote_host' => '127.0.0.1',
                    'identity' => '-',
                    'remote_user' => 'frank',
                    'time' => '2000-10-10T13:55:36-0700',
                    'request_method' => 'GET',
                    'request_path' => '/apache_pb.gif',
                    'request_protocol' => 'HTTP/1.0',
                    'response_code' => '200',
                    'bytes_sent' => '2326',
                )
            ),
            array(
                AccessLogParser::FORMAT_COMBINED,
                '127.0.0.1 - frank [10/Oct/2000:13:55:36 -0700] "GET /apache_pb.gif HTTP/1.0" 200 2326 ' .
                    '"http://www.example.com/start.html" "Mozilla/4.08 [en] (Win98; I ;Nav)"',
                array(
                    'remote_host' => '127.0.0.1',
                    'identity' => '-',
                    'remote_user' => 'frank',
                    'time' => '2000-10-10T13:55:36-0700',
                    'request_method' => 'GET',
                    'request_path' => '/apache_pb.gif',
                    'request_protocol' => 'HTTP/1.0',
                    'response_code' => '200',
                    'bytes_sent' => '2326',
                    'referer' => 'http://www.example.com/start.html',
                    'user_agent' => 'Mozilla/4.08 [en] (Win98; I ;Nav)',
                )
            ),
            array(
                AccessLogParser::FORMAT_COMBINED,
                '127.0.0.1 - - [28/Dec/2013:19:03:49 +0200] "GET /test-page/ HTTP/1.1" ' .
                    '200 8359 "-" "Symfony2 BrowserKit"',
                array(
                    'remote_host' => '127.0.0.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '2013-12-28T19:03:49+0200',
                    'request_method' => 'GET',
                    'request_path' => '/test-page/',
                    'request_protocol' => 'HTTP/1.1',
                    'response_code' => '200',
                    'bytes_sent' => '8359',
                    'referer' => '-',
                    'user_agent' => 'Symfony2 BrowserKit',
                )
            ),
            array(
                AccessLogParser::FORMAT_COMBINED,
                '66.249.78.230 - - [29/Dec/2013:16:07:58 +0200] "GET /robots.txt HTTP/1.1" ' .
                    '200 408 "-" "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)"',
                array(
                    'remote_host' => '66.249.78.230',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '2013-12-29T16:07:58+0200',
                    'request_method' => 'GET',
                    'request_path' => '/robots.txt',
                    'request_protocol' => 'HTTP/1.1',
                    'response_code' => '200',
                    'bytes_sent' => '408',
                    'referer' => '-',
                    'user_agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                )
            ),
            array(
                AccessLogParser::FORMAT_COMBINED,
                '71.82.1.1 - - [29/Dec/2013:17:37:40 +0200] "GET / HTTP/1.1" 200 2577 "http://example.com/test/" ' .
                    '"Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko"',
                array(
                    'remote_host' => '71.82.1.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '2013-12-29T17:37:40+0200',
                    'request_method' => 'GET',
                    'request_path' => '/',
                    'request_protocol' => 'HTTP/1.1',
                    'response_code' => '200',
                    'bytes_sent' => '2577',
                    'referer' => 'http://example.com/test/',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
                )
            ),
            array(
                AccessLogParser::FORMAT_COMBINED,
                // HTTP error 408 "Request Timeout"
                '192.168.25.1 - - [25/Jun/2012:14:00:14 -0700] "-" 408 0 "-" "-"',
                array(
                    'remote_host' => '192.168.25.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '2012-06-25T14:00:14-0700',
                    'response_code' => '408',
                    'referer' => '-',
                    'user_agent' => '-',
                )
            ),
            array(
                AccessLogParser::FORMAT_COMBINED,
                // HTTP error 414 "Request URI too long"
                '192.168.139.1 - - [10/Oct/2013:00:26:32 +0300] "GET /' . str_repeat('a', 7995) . '" 414 540 "-" "-"',
                array(
                    'remote_host' => '192.168.139.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '2013-10-10T00:26:32+0300',
                    'request_method' => 'GET',
                    'request_path' => '/' . str_repeat('a', 7995),
                    'response_code' => '414',
                    'bytes_sent' => '540',
                    'referer' => '-',
                    'user_agent' => '-',
                )
            ),
            array(
                AccessLogParser::FORMAT_VHOST_COMBINED,
                '127.0.1.1:80 127.0.0.1 - - [26/Jun/2012:10:41:10 -0700] "OPTIONS * HTTP/1.0" 200 126 "-" ' .
                    '"Apache/2.2.22 (Ubuntu) (internal dummy connection)"',
                array(
                    'server_name' => '127.0.1.1',
                    'server_port' => '80',
                    'remote_host' => '127.0.0.1',
                    'identity' => '-',
                    'remote_user' => '-',
                    'time' => '2012-06-26T10:41:10-0700',
                    'request_method' => 'OPTIONS',
                    'request_path' => '*',
                    'request_protocol' => 'HTTP/1.0',
                    'response_code' => '200',
                    'bytes_sent' => '126',
                    'referer' => '-',
                    'user_agent' => 'Apache/2.2.22 (Ubuntu) (internal dummy connection)',
                )
            ),
            array(
                // Test for percent sign
                '%% test',
                '% test',
                array()
            ),
            array(
                // Test for full port support
                '%{canonical}p %{local}p %{remote}p test',
                '123 456 789 test',
                array(
                    'canonical_port' => 123,
                    'local_port' => 456,
                    'remote_port' => 789,
                )
            ),
            array(
                // Test for status code of the original request
                '%s',
                '201',
                array(
                    'original_status_code' => 201,
                )
            ),
            array(
                // Test for Size of response in bytes, excluding HTTP headers. In CLF format
                '%b',
                '-',
                array(
                    'response_body_size' => 0,
                )
            ),
            array(
                // Test for predefined User-Agent format
                AccessLogParser::FORMAT_AGENT,
                'Symfony2 BrowserKit',
                array(
                    'user_agent' => 'Symfony2 BrowserKit',
                )
            ),
            array(
                // Test for request method
                '%m',
                'POST',
                array(
                    'request_method' => 'POST',
                )
            ),
            array(
                // Test for request serve time
                '%D %T',
                '123 456',
                array(
                    'request_time_us' => '123',
                    'request_time_s' => '456',
                )
            ),
            array(
                // Test for URL path
                '%U',
                '/path',
                array(
                    'request_path' => '/path',
                )
            ),
            array(
                // Test for URL path and query string
                '%U%q',
                '/path?googleguy=googley',
                array(
                    'request_path' => '/path',
                    'query_string' => '?googleguy=googley',
                )
            ),
            array(
                // Test for bytes received and transferred
                '%I %S',
                '123 456',
                array(
                    'bytes_received' => '123',
                    'bytes_transferred' => '456',
                )
            ),
            array(
                // Test for predefined referer log format
                AccessLogParser::FORMAT_REFERER,
                'http://www.example.com/ -> /my-page/',
                array(
                    'referer' => 'http://www.example.com/',
                    'request_path' => '/my-page/',
                )
            ),
            array(
                // Test for request protocol
                '%H',
                'HTTP/1.0',
                array(
                    'request_protocol' => 'HTTP/1.0',
                )
            ),
            array(
                // Test for local IP address
                '%A',
                '192.168.5.128',
                array(
                    'local_ip' => '192.168.5.128',
                )
            ),
            array(
                // Test for client IP address
                '%a',
                '192.168.5.1',
                array(
                    'client_ip' => '192.168.5.1',
                )
            ),
            array(
                // Test for real client IP address (e.g. when proxy is used)
                '%{c}a',
                '192.168.5.55',
                array(
                    'peer_ip' => '192.168.5.55',
                )
            ),
        );
    }

    /**
     * Data provider for testParseLineNoMatches()
     *
     * @return array[]
     */
    public function getTestParseLineNoMatchesData()
    {
        return array(
            array('%b', 'abc'),
            array('%B', '-'),
        );
    }
}
