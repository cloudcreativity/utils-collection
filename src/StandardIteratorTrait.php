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
        return $this->stack->all();
    }

    /**
     * Fluent clone.
     *
     * @return static
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * @return Collection
     */
    public function collect()
    {
        return clone $this->stack;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function each(callable $callable)
    {
        $this->stack->each($callable);

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function first()
    {
        return $this->stack->first();
    }

    /**
     * @return mixed|null
     */
    public function last()
    {
        return $this->stack->last();
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback)
    {
        $filtered = new static();
        $filtered->stack = $this->stack->filter($callback);

        return $filtered;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function reject(callable $callback)
    {
        $filtered = new static();
        $filtered->stack = $this->stack->reject($callback);

        return $filtered;
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function every(callable $callback)
    {
        return $this->stack->every($callback);
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function any(callable $callback)
    {
        return $this->stack->any($callback);
    }

    /**
     * @param callable $callable
     * @return Collection
     */
    public function map(callable $callable)
    {
        return $this->stack->map($callable);
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
