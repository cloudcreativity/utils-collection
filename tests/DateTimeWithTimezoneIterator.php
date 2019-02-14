<?php

namespace CloudCreativity\Utils\Collection\Tests;

use CloudCreativity\Utils\Collection\StandardIterator;

class DateTimeWithTimezoneIterator extends StandardIterator
{

    /**
     * @var \DateTimeZone
     */
    private $tz;

    /**
     * DateTimeWithTimezoneIterator constructor.
     *
     * @param \DateTimeZone $tz
     * @param \DateTime ...$dates
     */
    public function __construct(\DateTimeZone $tz, \DateTime ...$dates)
    {
        parent::__construct(...$dates);
        $this->tz = $tz;
        $this->stack = $this->stack->map(function (\DateTime $date) use ($tz) {
            $date = clone $date;
            $date->setTimezone($tz);
            return $date;
        });
    }

}
