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

use CloudCreativity\Utils\Collection\StandardIteratorInterface;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

/**
 * Class StandardIteratorTest
 *
 * @package CloudCreativity\Utils\Collection
 */
class StandardIteratorTest extends TestCase
{

    /**
     * @var TestIterator
     */
    private $iterator;

    protected function setUp()
    {
        $this->iterator = new TestIterator(['a', 'b', 'c']);
    }

    public function testIterator()
    {
        $this->assertEquals(['a', 'b', 'c'], iterator_to_array($this->iterator));
    }

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(StandardIteratorInterface::class, $this->iterator);
    }

    public function testGetAll()
    {
        $this->assertSame(['a', 'b', 'c'], $this->iterator->all());
    }

    public function testFirst()
    {
        $this->assertSame('a', $this->iterator->first());

        $this->expectException(OutOfBoundsException::class);
        (new TestIterator())->first();
    }

    public function testLast()
    {
        $this->assertSame('c', $this->iterator->last());

        $this->expectException(OutOfBoundsException::class);
        (new TestIterator())->last();
    }

    public function testFilter()
    {
        $expected = new TestIterator(['b', 'c']);

        $actual = $this->iterator->filter(function ($item, $index) {
            return $item !== 'a' && $index > 0;
        });

        $this->assertEquals($expected, $actual);
        $this->assertNotSame($this->iterator, $actual);
    }

    public function testReject()
    {
        $expected = new TestIterator(['a']);

        $actual = $this->iterator->reject(function ($item, $index) {
            return $item !== 'a' && $index > 0;
        });

        $this->assertEquals($expected, $actual);
        $this->assertNotSame($this->iterator, $actual);
    }

    public function testEvery()
    {
        $this->assertTrue($this->iterator->every(function ($item, $index) {
            return is_string($item) && $index < 3;
        }));

        $this->assertFalse($this->iterator->every(function ($item) {
            return in_array($item, ['a', 'b']);
        }));
    }

    public function testAny()
    {
        $this->assertTrue($this->iterator->any(function ($item) {
            return 'c' === $item;
        }));

        $this->assertFalse($this->iterator->any(function ($item, $index) {
            return 3 < $index;
        }));
    }

    public function testIsEmpty()
    {
        $this->assertFalse($this->iterator->isEmpty());
        $this->assertTrue((new TestIterator())->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $this->assertTrue($this->iterator->isNotEmpty());
        $this->assertFalse((new TestIterator())->isNotEmpty());
    }

    public function testCount()
    {
        $this->assertSame(3, $this->iterator->count());
    }
}