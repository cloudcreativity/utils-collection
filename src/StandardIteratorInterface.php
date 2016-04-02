<?php

/**
 * Copyright 2016 Cloud Creativity Limited
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
use OutOfBoundsException;

interface StandardIteratorInterface extends IteratorAggregate, Countable
{

    /**
     * @return array
     */
    public function getAll();

    /**
     * @return mixed
     * @throws OutOfBoundsException
     *      if the collection is empty.
     */
    public function first();

    /**
     * @return mixed
     * @throws OutOfBoundsException
     *      if the collection is empty.
     */
    public function last();

    /**
     * @param callable $callback
     * @return StandardIteratorInterface
     */
    public function filter(callable $callback);

    /**
     * @param callable $callback
     * @return StandardIteratorInterface
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
     * @return bool
     */
    public function isEmpty();
}
