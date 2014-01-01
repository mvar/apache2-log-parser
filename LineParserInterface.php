<?php
/**
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

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
