<?php

/*
 * (c) Mantas Varatiejus <var.mantas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MVar\Apache2LogParser;

/**
 * This trait defines logic needed to convert time to different format.
 */
trait TimeFormatTrait
{
    /**
     * @var bool|string Custom time format
     */
    private $timeFormat;

    /**
     * Sets time format.
     *
     * Set format string supported by PHP's date() function or set TRUE to get
     * time as \DateTime object.
     *
     * @param bool|string $timeFormat
     */
    public function setTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;
    }

    /**
     * Converts time to previously set format.
     *
     * @param string $time
     *
     * @return \DateTime|string
     */
    public function formatTime($time)
    {
        if ($this->timeFormat === null || $this->timeFormat === false) {
            return $time;
        }

        $dateTime = new \DateTime($time);

        if ($this->timeFormat === true) {
            return $dateTime;
        }

        return $dateTime->format($this->timeFormat);
    }
}
