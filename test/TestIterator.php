<?php

namespace CloudCreativity\Utils\Collection;

class TestIterator extends AbstractStandardIterator
{

    /**
     * @param array $items
     * @return $this
     */
    public function addMany(array $items)
    {
        $this->stack->addMany($items);

        return $this;
    }
}
