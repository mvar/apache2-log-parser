<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

class ErrorLogParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        date_default_timezone_set('UTC');
    }

    /**
     * Test for parseLine()
     *
     * @param string $line
     * @param array  $expectedResult
     *
     * @dataProvider getTestParseLineData()
     */
    public function testParseLine($line, array $expectedResult)
    {
        $parser = new ErrorLogParser();
        $result = $parser->parseLine($line);

        $this->assertEquals($expectedResult, $result);
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
                '[Sat Dec 14 21:07:07 2013] [error] [client 127.0.0.1] ' .
                    'File does not exist: /home/user/project/skin/base',
                array(
                    'time' => '2013-12-14T21:07:07+0000',
                    'error_level' => 'error',
                    'client_ip' => '127.0.0.1',
                    'message' => 'File does not exist: /home/user/project/skin/base',
                )
            ),
            array(
                '[Sat Dec 21 17:33:53 2013] [notice] Apache/2.2.22 (Ubuntu) PHP/5.3.10-1ubuntu3.9 with ' .
                    'Suhosin-Patch mod_ssl/2.2.22 OpenSSL/1.0.1 configured -- resuming normal operations',
                array(
                    'time' => '2013-12-21T17:33:53+0000',
                    'error_level' => 'notice',
                    'message' => 'Apache/2.2.22 (Ubuntu) PHP/5.3.10-1ubuntu3.9 with ' .
                        'Suhosin-Patch mod_ssl/2.2.22 OpenSSL/1.0.1 configured -- resuming normal operations',
                )
            ),
            array(
                '[Sat Dec 28 16:55:56 2013] [error] [client 192.168.5.1] File does not exist: ' .
                    '/var/www/favicon.ico, referer: http://server.vm/',
                array(
                    'time' => '2013-12-28T16:55:56+0000',
                    'error_level' => 'error',
                    'client_ip' => '192.168.5.1',
                    'message' => 'File does not exist: /var/www/favicon.ico',
                    'referer' => 'http://server.vm/',
                )
            ),
        );
    }

    /**
     * Test that a new log line pattern can be set and the parser successfully parses a line by that pattern.
     *
     * @param string $line
     * @param array  $expectedResult
     *
     * @dataProvider provideCustomPatternLineData
     * @throws Exception\NoMatchesException
     * @throws Exception\ParserException
     */
    public function testNewPatternCanBeSet($line, array $expectedResult)
    {

        $format = '/\[(?<time>.+?)\] \[:(?<level>.+?)\] \[pid (?<pid>[0-9]+)\] '
            . '\[client (?<ip>[0-9.]+):(?<port>[0-9]+)\] (?<message>.*)/';
        $parser = new ErrorLogParser($format);

        $result = $parser->parseLine($line);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array Data for testNewPatternCanBeSet
     */
    public function provideCustomPatternLineData()
    {

        return array(
            array(
                '[Wed Oct 08 16:25:01 2014] [:error] [pid 15359] [client 127.0.0.1:41952] '
                . 'PHP Error 1 Class "LogQueue" not found',
                array(
                    'time'    => '2014-10-08T16:25:01+0000',
                    'level'   => 'error',
                    'pid'     => '15359',
                    'ip'      => '127.0.0.1',
                    'port'    => '41952',
                    'message' => 'PHP Error 1 Class "LogQueue" not found'
                )
            ),
        );
    }
}
