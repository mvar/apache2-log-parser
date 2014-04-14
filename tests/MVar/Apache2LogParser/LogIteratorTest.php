<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

use MVar\Apache2LogParser\Exception\NoMatchesException;

class LogIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Creates and returns instance of parser mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|LineParserInterface
     */
    protected function getParser()
    {
        return $this->getMock('\\MVar\\Apache2LogParser\\LineParserInterface');
    }

    /**
     * Test for log iterator
     *
     * @param string $logfile
     * @param int    $rowsCount
     *
     * @dataProvider getTestIteratorData()
     */
    public function testIterator($logfile, $rowsCount)
    {
        $parser = $this->getParser();
        $expectedData = 'parsed_line';

        // Test if parser was called as many times as expected
        $parser->expects($this->exactly($rowsCount))
            ->method('parseLine')
            ->willReturn($expectedData);

        $iterator = new LogIterator($logfile, $parser);

        foreach ($iterator as $line => $data) {
            $this->assertTrue(is_string($line));
            $this->assertEquals($data, $expectedData);
        }
    }

    /**
     * Test for iterator in case of empty lines in log
     */
    public function testIteratorWithEmptyLines()
    {
        $parser = $this->getParser();

        $parser->expects($this->exactly(3))
            ->method('parseLine')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue('parsed_line'),
                    $this->throwException(new NoMatchesException()),
                    $this->returnValue('parsed_line')
                )
            );

        $iterator = new LogIterator(__DIR__ . '/Fixtures/access.log', $parser, false);

        $result = array();
        foreach ($iterator as $data) {
            $result[] = $data;
        }

        // Test if empty line was not parsed (NULL)
        $expectedResult = array('parsed_line', null, 'parsed_line');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test for iterator in case of file handler exception
     *
     * @expectedException \MVar\Apache2LogParser\Exception\ParserException
     * @expectedExceptionMessage Can not open log file
     */
    public function testIteratorFileException()
    {
        $iterator = new LogIterator(__DIR__ . '/Fixtures/non_existing_file.log', $this->getParser());
        $iterator->rewind();
    }

    /**
     * Data provider for testIterator()
     *
     * @return array[]
     */
    public function getTestIteratorData()
    {
        return array(
            // Simple log
            array(__DIR__ . '/Fixtures/access.log', 2),
            // Compressed log
            array('compress.zlib://file://' . __DIR__ . '/Fixtures/access_compressed.gz', 4),
        );
    }
}
