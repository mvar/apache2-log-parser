<?php
/**
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

class AccessLogParser extends AbstractLineParser
{
    // Copied from Apache 2.2.22 config
    const FORMAT_COMMON = '%h %l %u %t "%r" %>s %O';
    const FORMAT_COMBINED = '%h %l %u %t "%r" %>s %O "%{Referer}i" "%{User-Agent}i"';
    const FORMAT_VHOST_COMBINED = '%v:%p %h %l %u %t "%r" %>s %O "%{Referer}i" "%{User-Agent}i"';
    const FORMAT_REFERER = '%{Referer}i -> %U';
    const FORMAT_AGENT = '%{User-Agent}i';

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
    protected function prepareParsedData(array $matches)
    {
        // Remove indexed values
        $filtered = array_filter(array_keys($matches), 'is_string');
        $result = array_intersect_key($matches, array_flip($filtered));
        $result = array_filter($result);

        if (isset($result['time'])) {
            // Convert date format to ISO
            $date = new \DateTime($result['time']);
            $result['time'] = $date->format(\DateTime::ISO8601);
        }

        if (isset($result['response_body_size']) && $result['response_body_size'] == '-') {
            $result['response_body_size'] = 0;
        }

        // Put all cookies to single array
        foreach ($result as $key => $data) {
            $search = 'cookies';
            if (($pos = strpos($key, "{$search}__")) === 0) {
                $result[$search][substr($key, strlen($search) + 2)] = $data;
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
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
     * Note: This parser is not a validator, so in most cases patterns must not be exact
     *
     * @return array
     */
    protected function getSimplePatterns()
    {
        return array(
            // The percent sign
            '%%' => '%',
            // Local IP address
            '%A' => '(?<local_ip>[\dA-Za-z\:\.]{3,39})',
            // Client IP address of the request
            '%a' => '(?<client_ip>[\dA-Za-z\:\.]{3,39})',
            // Underlying peer IP address of the connection
            '%{c}a' => '(?<peer_ip>[\dA-Za-z\:\.]{3,39})',
            // Size of response in bytes, excluding HTTP headers
            '%B' => '(?<response_body_size>\d+)',
            // Size of response in bytes, excluding HTTP headers. In CLF format
            '%b' => '(?<response_body_size>\d+|-)',
            // The time taken to serve the request, in microseconds
            '%D' => '(?<request_time_us>\d+)',
            // Filename
            '%f' => '(?<filename>.+)',
            // The request protocol
            '%H' => '(?<request_protocol>\S+)',
            // Remote hostname
            '%h' => '(?<remote_host>\S+)',
            // Bytes received, including request and headers
            '%I' => '(?<bytes_received>\d+)',
            // Remote logname
            '%l' => '(?<identity>\S+)',
            // The request method
            '%m' => '(?<request_method>[A-Za-z]+)',
            // Bytes sent, including headers
            '%O' => '(?<bytes_sent>\d+)',
            // The canonical port of the server serving the request
            '%p' => '(?<server_port>\d+)',
            // The query string
            '%q' => '(?<query_string>\?\S+|)',
            // First line of request
            '%r' => '((?<request_method>\w+) (?<request_path>\S+)( (?<request_protocol>\S+))?|-)',
            // Bytes transferred (received and sent), including request and headers
            '%S' => '(?<bytes_transferred>\d+)',
            // The status of the original request
            '%s' => '(?<original_status_code>[2-5]\d\d)',
            // Status of the final request
            '%>s' => '(?<response_code>[2-5]\d\d)',
            // The time taken to serve the request, in seconds
            '%T' => '(?<request_time_s>\d+)',
            // Time the request was received
            '%t' => '\[(?<time>\d\d\/\w{3}\/\d{4}\:\d\d\:\d\d\:\d\d [+-]\d{4})\]',
            // The URL path requested, not including any query string
            '%U' => '(?<request_path>\S+?)',
            // Remote user
            '%u' => '(?<remote_user>\S+)',
            // The canonical ServerName of the server serving the request
            '%v' => '(?<server_name>\S+)',
            // Connection status when response is completed
            '%X' => '(?<connection_status>[Xx]|\+|\-)',
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
                $pattern = $header == 'referer' ? '\S+' : '.+';
                return "(?<{$header}>{$pattern})";
            },
            // The contents of cookies in the request sent to the server
            '/%\{(\S+)\}C/' => function (array $matches) {
                return "(?<cookies__{$matches[1]}>.+)";
            },
            // The canonical port of the server serving the request, or the server's actual port,
            // or the client's actual port
            '/%\{(canonical|local|remote)\}p/' => function (array $matches) {
                return '(?<' . $matches[1] . '_port>\d+)';
            },
        );
    }
}
