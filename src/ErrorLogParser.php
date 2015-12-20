<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

/**
 * Apache 2.2 and older error log parser.
 */
class ErrorLogParser extends AbstractLineParser
{
    use TimeFormatTrait;

    /**
     * {@inheritdoc}
     */
    protected function prepareParsedData(array $matches)
    {
        $result = parent::prepareParsedData($matches);

        // Convert time
        $result['time'] = $this->formatTime($result['time']);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPattern()
    {
        $pattern = '/\[(?<time>.+)\] \[(?<error_level>\w+)\]( \[client\ (?<client_ip>.+)])? ' .
            '(?<message>.+(?=, referer)|.+)(, referer: (?<referer>.+))?/';

        return $pattern;
    }
}
