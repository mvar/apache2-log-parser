<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

/**
 * Apache 2.2 and older error log parser
 */
class ErrorLogParser extends AbstractLineParser
{

    /**
     * @var string Holds the pattern against which to parse error log lines
     */
    protected $_pattern;

    /**
     * @param null|string $pattern Error log line pattern or null to use the default
     */
    public function __construct($pattern = null)
    {

        $this->_pattern = $pattern ?: '/\[(?<time>.+)\] \[(?<error_level>\w+)\]( \[client\ (?<client_ip>.+)])? ' .
            '(?<message>.+(?=, referer)|.+)(, referer: (?<referer>.+))?/';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareParsedData(array $matches)
    {
        // Remove indexed values
        $filtered = array_filter(array_keys($matches), 'is_string');
        $result = array_intersect_key($matches, array_flip($filtered));
        $result = array_filter($result);

        // Convert date format to ISO
        $date = new \DateTime($result['time']);
        $result['time'] = $date->format(\DateTime::ISO8601);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPattern()
    {
        return $this->_pattern;
    }
}
