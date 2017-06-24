<?php

namespace CloudCreativity\Utils\Collection\Tests;

use CloudCreativity\Utils\Collection\Collection;
use CloudCreativity\Utils\Collection\StandardIterator;
use DateTime;

class DateTimeIterator extends StandardIterator
{

    /**
     * DateTimeIterator constructor.
     *
     * @param DateTime[] ...$items
     */
    public function __construct(DateTime ...$items)
    {
        parent::__construct(...$items);
    }

    /**
     * @param $format
     * @return Collection
     */
    public function format($format)
    {
        return $this->stack->invoke('format', $format);
    }
}
