<?php

namespace MVar\Apache2LogParser\Tests;

use MVar\Apache2LogParser\ErrorLogParser;

class ErrorLogParserTest extends \PHPUnit_Framework_TestCase
{
//    public function testParseLine($line, array $expectedResult)
//    {
//
//    }

    /**
     * @expectedException \MVar\Apache2LogParser\ParserException
     * @expectedExceptionMessage must be a string
     */
    public function testParseLineExceptionArgument()
    {
        $parser = new ErrorLogParser();
        $parser->parseLine(array(123, 456));
    }
} 
