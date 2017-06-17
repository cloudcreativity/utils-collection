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

namespace CloudCreativity\Utils\Collection\Tests;

use CloudCreativity\Utils\Collection\AbstractStandardIterator;

/**
 * Class TestIterator
 *
 * @package CloudCreativity\Utils\Collection
 */
class TestIterator extends AbstractStandardIterator
{

    /**
     * TestIterator constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct();
        $this->addMany($items);
    }

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