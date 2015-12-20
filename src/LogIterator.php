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
 * This is the class that helps to iterate through log file.
 *
 * @deprecated Will be removed in 3.0. Use \MVar\LogParser\LogIterator instead.
 */
class LogIterator extends \MVar\LogParser\LogIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getFileHandler()
    {
        try {
            return parent::getFileHandler();
        } catch (\MVar\LogParser\Exception\ParserException $exception) {
            throw new ParserException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
