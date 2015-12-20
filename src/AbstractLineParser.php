<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

use MVar\Apache2LogParser\Exception\NoMatchesException;
use MVar\Apache2LogParser\Exception\ParserException;

/**
 * Abstract line parser.
 *
 * @deprecated Will be removed in 3.0. Use \MVar\LogParser\AbstractLineParser instead.
 */
abstract class AbstractLineParser extends \MVar\LogParser\AbstractLineParser implements LineParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseLine($line)
    {
        if (!is_string($line)) {
            throw new ParserException('Parser argument must be a string.');
        }

        $match = @preg_match($this->getPattern(), $line, $matches);

        if ($match === false) {
            $error = error_get_last();
            throw new ParserException("Matcher failure. Please check if given format is valid. ({$error["message"]})");
        }

        if (!$match) {
            throw new NoMatchesException('Given line does not match predefined pattern.');
        }

        return $this->prepareParsedData($matches);
    }
}
