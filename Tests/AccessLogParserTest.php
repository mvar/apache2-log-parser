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
                    'client_ip' => '127.0.0.1',
                    'identity' => '-',
                    'user_id' => 'frank',
                    'time' => '2000-10-10T13:55:36-0700',
                    'request_method' => 'GET',
                    'request_file' => '/apache_pb.gif',
                    'request_protocol' => 'HTTP/1.0',
                    'response_code' => '200',
                    'response_body_size' => '2326',
                )
            ),
        );
    }
}
