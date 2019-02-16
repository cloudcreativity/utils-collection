<?php
/**
 * Copyright 2019 Cloud Creativity Limited
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

use Countable;
use IteratorAggregate;

/**
 * Interface StandardIteratorInterface
 *
 * @package CloudCreativity\Utils\Collection
 */
interface StandardIteratorInterface extends IteratorAggregate, Countable
{

    /**
     * @return array
     */
    public function all();

    /**
     * Fluent clone.
     *
     * @return static
     */
    public function copy();

    /**
     * @return Collection
     */
    public function collect();

    /**
     * @param callable $callback
     * @return $this
     */
    public function each(callable $callback);

    /**
     * @param callable|null $callback
     * @return mixed|null
     */
    public function first(callable $callback = null);

    /**
     * @param callable|null $callback
     * @return mixed|null
     */
    public function last(callable $callback = null);

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback);

    /**
     * @param callable $callback
     * @return static
     */
    public function reject(callable $callback);

    /**
     * @param callable $callback
     * @return bool
     */
    public function every(callable $callback);

    /**
     * @param callable $callback
     * @return bool
     */
    public function any(callable $callback);

    /**
     * @param callable $callback
     * @return Collection
     */
    public function map(callable $callback);

    /**
     * @param $amount
     * @return static
     */
    public function take($amount);

    /**
     * @param callable $callback
     * @return static
     */
    public function tap(callable $callback);

    /**
     * @param callable $callback
     * @return static
     */
    public function sort(callable $callback);

    /**
     * @param $method
     * @param mixed ...$args
     * @return Collection
     * @todo add for 2.0
     *
    public function invoke($method, ...$args);
     */

    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * @return bool
     */
    public function isNotEmpty();
}
