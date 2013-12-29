<?php

namespace MVar\Apache2LogParser;

interface ParserInterface
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
