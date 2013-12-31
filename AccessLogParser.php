<?php
/**
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

class AccessLogParser implements ParserInterface
{
    // Copied from Apache 2.2.22 config
    const FORMAT_COMMON = '%h %l %u %t "%r" %>s %O';
    const FORMAT_COMBINED = '%h %l %u %t "%r" %>s %O "%{Referer}i" "%{User-Agent}i"';

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * Constructor
     *
     * @param string $format
     */
    public function __construct($format)
    {
        $this->format = $format;
    }

    /**
     * {@inheritDoc}
     */
    public function parseLine($line)
    {
        if (!is_string($line)) {
            throw new ParserException('Parser argument must be a string.');
        }

        if (!preg_match($this->getPattern(), $line, $matches)) {
            throw new ParserException('Given line does not match predefined pattern.');
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
        if ($this->pattern !== null) {
            return $this->pattern;
        }

        $pattern = $this->format;

        // Put simple patterns
        $pattern = str_replace(
            array_keys($this->getSimplePatterns()),
            array_values($this->getSimplePatterns()),
            $pattern
        );

        // Put regexp patterns
        foreach ($this->getCallbackPatterns() as $callbackPattern => $callback) {
            $pattern = preg_replace_callback($callbackPattern, $callback, $pattern);
        }

        $this->pattern = "/^{$pattern}$/";

        return $this->pattern;
    }

    /**
     * Returns patters that can be replaced with as strings.
     * Note: This parser is not validator, so in most cases patterns must not be exact
     *
     * @return array
     */
    protected function getSimplePatterns()
    {
        return array(
            '%h' => '(?<client_ip>\S+)',
            '%l' => '(?<identity>\S+)',
            '%u' => '(?<user_id>\S+)',
            '%t' => '\[(?<time>\d\d\/\w{3}\/\d{4}\:\d\d\:\d\d\:\d\d [+-]\d{4})\]',
            '%r' => '((?<request_method>\w+) (?<request_file>\S+)( (?<request_protocol>\S+))?|-)',
            '%>s' => '(?<response_code>[2-5]\d\d)',
            '%b' => '(?<response_body_size>\d+)',
            // Bytes sent, including headers
            '%O' => '(?<bytes_sent>\d+)',
        );
    }

    /**
     * Patterns that requires preg_replace_callback() to be set in place
     *
     * @return array
     */
    protected function getCallbackPatterns()
    {
        return array(
            // Header lines in the request sent to the server (e.g., User-Agent, Referer)
            '/%\{([A-Za-z0-9]+(\-[A-Za-z0-9]+)*)\}i/' => function (array $matches) {
                $header = strtolower(str_replace('-', '_', $matches[1]));
                return "(?<{$header}>.+)";
            },
        );
    }
}
