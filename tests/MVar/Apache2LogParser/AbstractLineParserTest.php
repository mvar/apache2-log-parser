<?php
/**
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

class AbstractLineParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Creates and returns instance of AbstractLineParser mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractLineParser
     */
    protected function getParser()
    {
        $mock = $this->getMock(
            '\\MVar\\Apache2LogParser\\AbstractLineParser',
            array('prepareParsedData', 'getPattern')
        );

        return $mock;
    }

    /**
     * Test for parseLine() in case of exception when passed incorrect argument
     *
     * @expectedException \MVar\Apache2LogParser\Exception\ParserException
     * @expectedExceptionMessage must be a string
     */
    public function testParseLineExceptionArgument()
    {
        $parser = $this->getParser();
        $parser->parseLine(array(123, 456));
    }

    /**
     * Test for parseLine() in case of exception when matcher fails
     *
     * @expectedException \MVar\Apache2LogParser\Exception\ParserException
     * @expectedExceptionMessage Matcher failure
     */
    public function testParseLineExceptionMatcherFailure()
    {
        $parser = $this->getParser();
        $parser->expects($this->once())->method('getPattern')->will($this->returnValue('invalid_regexp_pattern'));

        $parser->parseLine('test string');
    }

    /**
     * Test for parseLine() in case of exception when no matches were found
     *
     * @expectedException \MVar\Apache2LogParser\Exception\NoMatchesException
     * @expectedExceptionMessage line does not match
     */
    public function testParseLineExceptionNoMatches()
    {
        $parser = $this->getParser();
        $parser->expects($this->once())->method('getPattern')->will($this->returnValue('/\d+/'));

        $parser->parseLine('test string');
    }

    /**
     * Test for parseLine()
     */
    public function testParseLine()
    {
        $parser = $this->getParser();
        $parser->expects($this->once())->method('getPattern')->will($this->returnValue('/.*/'));

        $parser->parseLine('test string');
    }
}
