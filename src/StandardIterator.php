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

use Traversable;

/**
 * Class AbstractStandardIterator
 *
 * @package CloudCreativity\Utils\Collection
 */
class StandardIterator implements StandardIteratorInterface
{

    use StandardIteratorTrait;

    /**
     * @param array ...$items
     * @return static
     */
    public static function create(...$items)
    {
        return new static(...$items);
    }

    /**
     * @param StandardIterator|array|Traversable $items
     * @return static
     */
    public static function cast($items)
    {
        if ($items instanceof static) {
            return $items;
        }

        return new static(...$items);
    }

    /**
     * StandardIterator constructor.
     *
     * @param array ...$items
     */
    public function __construct(...$items)
    {
        $this->stack = new Collection(...$items);
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->stack = clone $this->stack;
    }

}
