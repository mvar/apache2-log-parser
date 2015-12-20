<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser\Tests;

use MVar\Apache2LogParser\ErrorLogParser;

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
     * Test for parseLine().
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
     * Data provider for testParseLine().
     *
     * @return array[]
     */
    public function getTestParseLineData()
    {
        return [
            [
                '[Sat Dec 14 21:07:07 2013] [error] [client 127.0.0.1] ' .
                    'File does not exist: /home/user/project/skin/base',
                [
                    'time' => 'Sat Dec 14 21:07:07 2013',
                    'error_level' => 'error',
                    'client_ip' => '127.0.0.1',
                    'message' => 'File does not exist: /home/user/project/skin/base',
                ],
            ],
            [
                '[Sat Dec 21 17:33:53 2013] [notice] Apache/2.2.22 (Ubuntu) PHP/5.3.10-1ubuntu3.9 with ' .
                    'Suhosin-Patch mod_ssl/2.2.22 OpenSSL/1.0.1 configured -- resuming normal operations',
                [
                    'time' => 'Sat Dec 21 17:33:53 2013',
                    'error_level' => 'notice',
                    'message' => 'Apache/2.2.22 (Ubuntu) PHP/5.3.10-1ubuntu3.9 with ' .
                        'Suhosin-Patch mod_ssl/2.2.22 OpenSSL/1.0.1 configured -- resuming normal operations',
                ],
            ],
            [
                '[Sat Dec 28 16:55:56 2013] [error] [client 192.168.5.1] File does not exist: ' .
                    '/var/www/favicon.ico, referer: http://server.vm/',
                [
                    'time' => 'Sat Dec 28 16:55:56 2013',
                    'error_level' => 'error',
                    'client_ip' => '192.168.5.1',
                    'message' => 'File does not exist: /var/www/favicon.ico',
                    'referer' => 'http://server.vm/',
                ],
            ],
        ];
    }
}
