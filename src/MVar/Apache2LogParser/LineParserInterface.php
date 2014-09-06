<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

use MVar\Apache2LogParser\Exception\ParserException;

/**
 * This is the interface for single log line parser
 */
interface LineParserInterface
{
    /**
     * Parses single log line
     *
     * @param string $line
     *
     * @return array
     * @throws ParserException
     */
    public function parseLine($line);
}
