<?php

namespace CloudCreativity\Utils\Collection;

use OutOfBoundsException;

trait StandardIteratorTrait
{

    /**
     * @var Collection|null
     */
    protected $stack;

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->stack()->toArray();
    }

    /**
     * @return mixed
     * @throws OutOfBoundsException
     *      if the collection is empty.
     */
    public function first()
    {
        if ($this->isEmpty()) {
            throw new OutOfBoundsException('No items.');
        }

        return $this->stack()->first();
    }

    /**
     * @return mixed
     * @throws OutOfBoundsException
     *      if the collection is empty.
     */
    public function last()
    {
        if ($this->isEmpty()) {
            throw new OutOfBoundsException('No items.');
        }

        return $this->stack()->last();
    }

    /**
     * @param callable $callback
     * @return StandardIterator
     */
    public function filter(callable $callback)
    {
        $filtered = new static();
        $filtered->stack = $this->stack()->filter($callback);

        return $filtered;
    }

    /**
     * @param callable $callback
     * @return StandardIterator
     */
    public function reject(callable $callback)
    {
        $filtered = new static();
        $filtered->stack = $this->stack()->reject($callback);

        return $filtered;
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function every(callable $callback)
    {
        return $this->stack()->every($callback);
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function any(callable $callback)
    {
        return $this->stack()->any($callback);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->stack()->isEmpty();
    }

    /**
     * @return Collection
     */
    public function getIterator()
    {
        return $this->stack()->copy();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->stack()->count();
    }

    /**
     * @return Collection
     */
    protected function stack()
    {
        if (!$this->stack instanceof Collection) {
            $this->stack = new Collection();
        }

        return $this->stack;
    }
}
