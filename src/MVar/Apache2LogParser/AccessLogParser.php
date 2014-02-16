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
     * @var KeysHolder
     */
    protected $keysHolder;

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

        $this->formatTime($result);

        if (isset($result['response_body_size']) && $result['response_body_size'] == '-') {
            $result['response_body_size'] = 0;
        }

        foreach ($this->keysHolder->getNamespaces() as $search) {
            // Put all variables to single array
            foreach ($result as $key => $data) {
                if (strpos($key, "{$search}__") === 0) {
                    $realKey = substr($key, strlen($search) + 2);
                    $realKey = $this->keysHolder->get($search, $realKey) ?: $realKey;
                    $result[$search][$realKey] = $data;
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

    /**
     * Convert date format to ISO
     *
     * @param array $result
     */
    protected function formatTime(array &$result)
    {
        if (isset($result['time'])) {
            $date = new \DateTime($result['time']);
            $result['time'] = $date->format(\DateTime::ISO8601);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getPattern()
    {
        if ($this->pattern !== null) {
            return $this->pattern;
        }

        $this->keysHolder = new KeysHolder();
        $pattern = $this->getQuotedFormatString();

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
     * Quotes characters which are not included in log format directives
     * and returns quoted format string
     *
     * @return string
     */
    protected function getQuotedFormatString()
    {
        // Valid pattern of log format directives
        $validPattern = '%(\!?[2-5]\d\d(\,[2-5]\d\d)*)?(\<|\>)?(\{[^\}]*\})?[a-z]';

        $pattern = preg_replace_callback(
            '/(?<before>' . $validPattern . '?)?(?<match>.+?)(?<after>' . $validPattern . ')?/i',
            function (array $matches) {
                $before = isset($matches['before']) ? $matches['before'] : '';
                $after = isset($matches['after']) ? $matches['after'] : '';
                $match = preg_quote($matches['match'], '/');

                return "{$before}{$match}{$after}";
            },
            $this->format
        );

        return $pattern;
    }

    /**
     * Returns patters that can be replaced with as strings.
     * Note: This parser is not a validator, so in most cases patterns must not be exact
     *
     * @return array
     */
    protected function getSimplePatterns()
    {
        // Register "request" namespace in KeysHolder
        // This allows to convert parsed variables to array
        $this->keysHolder->registerNamespace('request');

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
            // Number of keep-alive requests handled on this connection
            '%k' => '(?<keepalive_requests>\d+)',
            // The request log ID from the error log
            '%L' => '(?<log_id>\S+)',
            // Remote logname
            '%l' => '(?<identity>\S+)',
            // The request method
            '%m' => '(?<request_method>[A-Za-z]+)',
            // Bytes sent, including headers
            '%O' => '(?<bytes_sent>\d+|\-)',
            // The process ID of the child that serviced the request
            '%P' => '(?<process_id>\S+)',
            // The canonical port of the server serving the request
            '%p' => '(?<server_port>\d+)',
            // The query string
            '%q' => '(?<query_string>\?\S+|)',
            // The handler generating the response
            '%R' => '(?<response_handler>\S+)',
            // First line of request
            '%r' => '(?<request_line>(?<request__method>\w+) (?<request__path>\S+)( (?<request__protocol>\S+))?|-)',
            // Bytes transferred (received and sent), including request and headers
            '%S' => '(?<bytes_transferred>\d+)',
            // The status of the original request
            '%s' => '(?<original_status_code>[2-5]\d\d)',
            // Status of the final request
            '%>s' => '(?<response_code>[2-5]\d\d)', // TODO: check after modifiers support implementation
            // The time taken to serve the request, in seconds
            '%T' => '(?<request_time_s>\d+)',
            // Time the request was received
            '%t' => '\[(?<time>\d\d\/\w{3}\/\d{4}\:\d\d\:\d\d\:\d\d [+-]\d{4})\]',
            // The URL path requested, not including any query string
            '%U' => '(?<request_path>\S+?)',
            // Remote user
            '%u' => '(?<remote_user>\S+)',
            // The server name according to the UseCanonicalName setting
            '%V' => '(?<server_name>\S+)',
            // The canonical ServerName of the server serving the request
            '%v' => '(?<canonical_server_name>\S+)',
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
        $holder = $this->keysHolder;

        return array(
            // Header lines in the request sent to the server (e.g., User-Agent, Referer)
            '/%\{([^\}]+)\}i/' => function (array $matches) use ($holder) {
                $index = $holder->add('request_headers', $matches[1]);
                $pattern = strcasecmp($matches[1], 'referer') == 0 ? '\S*' : '.+';

                return "(?<request_headers__{$index}>{$pattern})";
            },
            // The contents of cookies in the request sent to the server
            '/%\{([^\}]+)\}C/' => function (array $matches) use ($holder) {
                $index = $holder->add('cookies', $matches[1]);

                return "(?<cookies__{$index}>.+)";
            },
            // The contents of the environment variable
            '/%\{([^\}]+)\}e/' => function (array $matches) use ($holder) {
                $index = $holder->add('env_vars', $matches[1]);

                return "(?<env_vars__{$index}>.+)";
            },
            // The contents of notes from another modules
            '/%\{([^\}]+)\}n/' => function (array $matches) use ($holder) {
                $index = $holder->add('mod_vars', $matches[1]);

                return "(?<mod_vars__{$index}>.+)";
            },
            // Header lines in the response sent from the server
            '/%\{([^\}]+)\}o/' => function (array $matches) use ($holder) {
                $index = $holder->add('response_headers', $matches[1]);

                return "(?<response_headers__{$index}>.+)";
            },
            // The process ID or thread ID of the child that serviced the request
            '/%\{(pid|tid|hextid)\}P/' => function (array $matches) {
                return '(?<' . $matches[1] . '>\S+)';
            },
            // The canonical port of the server serving the request, or the server's actual port,
            // or the client's actual port
            '/%\{(canonical|local|remote)\}p/' => function (array $matches) {
                return '(?<' . $matches[1] . '_port>\d+)';
            },
        );
    }
}
