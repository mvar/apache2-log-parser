<?php

namespace MVar\Apache2LogParser;

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

        // TODO: Implement parseLine() method.
    }
}
