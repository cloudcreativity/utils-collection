<?php

namespace CloudCreativity\Utils\Collection;

abstract class AbstractStandardIterator
{

    use StandardIteratorTrait;

    /**
     * @param array £items
     * @return $this
     */
    abstract public function addMany(array $items);

    /**
     * AbstractStandardIterator constructor.
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->stack = new Collection();
        $this->addMany($items);
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->stack = clone $this->stack;
    }

}
