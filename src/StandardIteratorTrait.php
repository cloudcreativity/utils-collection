<?php

/**
 * Copyright 2017 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CloudCreativity\Utils\Collection;

use Closure;
use OutOfBoundsException;

/**
 * Trait StandardIteratorTrait
 *
 * @package CloudCreativity\Utils\Collection
 */
trait StandardIteratorTrait
{

    /**
     * @var Collection
     */
    protected $stack;

    /**
     * @return array
     */
    public function all()
    {
        return $this->stack->toArray();
    }

    /**
     * @return Collection
     */
    public function collect()
    {
        return clone $this->stack;
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

        return $this->stack->first();
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

        return $this->stack->last();
    }

    /**
     * @param Closure $callback
     * @return StandardIteratorInterface
     */
    public function filter(Closure $callback)
    {
        $filtered = new static();
        $filtered->stack = $this->stack->filter($callback);

        return $filtered;
    }

    /**
     * @param Closure $callback
     * @return StandardIteratorInterface
     */
    public function reject(Closure $callback)
    {
        $filtered = new static();
        $filtered->stack = $this->stack->reject($callback);

        return $filtered;
    }

    /**
     * @param Closure $callback
     * @return bool
     */
    public function every(Closure $callback)
    {
        return $this->stack->every($callback);
    }

    /**
     * @param Closure $callback
     * @return bool
     */
    public function any(Closure $callback)
    {
        return $this->stack->any($callback);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->stack->isEmpty();
    }

    /**
     * @return bool
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     * @return Collection
     */
    public function getIterator()
    {
        return $this->stack->copy();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->stack->count();
    }

}
