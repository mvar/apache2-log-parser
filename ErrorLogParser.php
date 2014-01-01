<?php
/**
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

/**
 * Apache 2.2 and older error log parser
 *
 * @package MVar\Apache2LogParser
 */
class ErrorLogParser implements ParserInterface
{
    /**
     * Parses single log line
     *
     * @param string $line
     *
     * @return array
     * @throws ParserException
     */
    public function parseLine($line)
    {
        if (!is_string($line)) {
            throw new ParserException('Parser argument must be a string.');
        }

        $match = preg_match($this->getPattern(), $line, $matches);

        if ($match === false) {
            throw new ParserException('Matcher failure. Please check if given format is valid.');
        }

        if (!$match) {
            throw new NoMatchesException('Given line does not match predefined pattern.');
        }

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
     * Returns pattern of log line
     *
     * @return string
     */
    protected function getPattern()
    {
        $pattern = '/\[(?<time>.+)\] \[(?<error_level>\w+)\]( \[client\ (?<client_ip>.+)])? ' .
            '(?<message>.+(?=, referer)|.+)(, referer: (?<referer>.+))?/';

        return $pattern;
    }
}
