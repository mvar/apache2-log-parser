<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser\Exception;

/**
 * This exception is thrown when parser is unable to parse given string.
 *
 * @deprecated Will be removed in 3.0. Use \MVar\LogParser\Exception\ParserException instead.
 */
class ParserException extends \MVar\LogParser\Exception\ParserException
{
}
