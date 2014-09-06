<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser\Exception;

/**
 * This exception is thrown when the string passed to parser does not match
 * expected pattern
 */
class NoMatchesException extends ParserException
{
}
