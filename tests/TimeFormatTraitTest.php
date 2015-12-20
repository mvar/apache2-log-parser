<?php

namespace MVar\Apache2LogParser\Tests;

use MVar\Apache2LogParser\TimeFormatTrait;

class TimeFormatTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for formatTime() in case no format was set.
     */
    public function testFormatTimeNoFormatting()
    {
        /** @var TimeFormatTrait $formatter */
        $formatter = $this->getMockForTrait('MVar\Apache2LogParser\TimeFormatTrait');

        $this->assertEquals('no formatting', $formatter->formatTime('no formatting'));
    }

    /**
     * Test for formatTime() in case object was requested.
     */
    public function testFormatTimeObject()
    {
        /** @var TimeFormatTrait $formatter */
        $formatter = $this->getMockForTrait('MVar\Apache2LogParser\TimeFormatTrait');
        $formatter->setTimeFormat(true);

        $dateTime = new \DateTime();

        $this->assertEquals($dateTime, $formatter->formatTime($dateTime->format(\DateTime::ISO8601)));
    }

    /**
     * Test for formatTime() in case custom format was set.
     */
    public function testFormatTimeCustom()
    {
        /** @var TimeFormatTrait $formatter */
        $formatter = $this->getMockForTrait('MVar\Apache2LogParser\TimeFormatTrait');
        $formatter->setTimeFormat('d/m/Y H:i:s');

        $this->assertEquals('05/12/2015 12:00:00', $formatter->formatTime('2015-12-05 12:00:00'));
    }
}
