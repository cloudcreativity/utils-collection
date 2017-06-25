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

use CloudCreativity\Utils\Collection\Collection;
use CloudCreativity\Utils\Collection\StandardIterator;
use CloudCreativity\Utils\Collection\StandardIteratorInterface;
use DateTime;
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
     * @var StandardIterator
     */
    private $iterator;

    protected function setUp()
    {
        $this->iterator = new StandardIterator('a', 'b', 'c');
    }

    public function testIterator()
    {
        $this->assertEquals(['a', 'b', 'c'], iterator_to_array($this->iterator));
    }

    public function testCreate()
    {
        $this->assertEquals($this->iterator, StandardIterator::create('a', 'b', 'c'));
        $date = new DateTime();
        $this->assertEquals([$date->format('c')], DateTimeIterator::create($date)->format('c')->all());
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
        $this->assertNull(StandardIterator::create()->first());
    }

    public function testLast()
    {
        $this->assertSame('c', $this->iterator->last());
        $this->assertNull(StandardIterator::create()->last());
    }

    public function testFilter()
    {
        $expected = new StandardIterator('b', 'c');

        $actual = $this->iterator->filter(function ($item, $index) {
            return $item !== 'a' && $index > 0;
        });

        $this->assertEquals($expected, $actual);
        $this->assertNotSame($this->iterator, $actual);
    }

    public function testReject()
    {
        $expected = new StandardIterator('a');

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
        $this->assertTrue((new StandardIterator())->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $this->assertTrue($this->iterator->isNotEmpty());
        $this->assertFalse((new StandardIterator())->isNotEmpty());
    }

    public function testCount()
    {
        $this->assertSame(3, $this->iterator->count());
    }

    public function testCopy()
    {
        $actual = $this->iterator->copy();

        $this->assertEquals($this->iterator, $actual);
        $this->assertNotSame($this->iterator, $actual);
    }

    public function testMap()
    {
        $actual = $this->iterator->map(function ($item) {
            return $item . $item;
        });

        $this->assertInstanceOf(Collection::class, $actual);
        $this->assertSame(['aa', 'bb', 'cc'], $actual->all());
        $this->assertSame(['a', 'b', 'c'], $this->iterator->all());
    }

    public function testEach()
    {
        $expected = new DateTime('2017-05-18 12:00:00');
        $dates = new DateTimeIterator(new DateTime());

        $dates->each(function (DateTime $date) use ($expected) {
            $date->setTimestamp($expected->getTimestamp());
        });

        $this->assertEquals([$expected], $dates->all());
    }

    public function testCastsItself()
    {
        $this->assertSame($this->iterator, StandardIterator::cast($this->iterator));
    }

    public function testCastUsesStatic()
    {
        $dates = new DateTimeIterator($date = new DateTime());
        $this->assertSame($dates, DateTimeIterator::cast($dates));
        $other = new StandardIterator($date);
        $this->assertEquals($dates, DateTimeIterator::cast($other));
        $this->assertEquals($dates, DateTimeIterator::cast([$date]));
    }

    public function testTap()
    {
        $tapped = null;

        $actual = $this->iterator->tap(function (StandardIterator $tap) use (&$tapped) {
            $this->assertNotSame($this->iterator, $tap);
            $tapped = $tap->take(2);
        });

        $this->assertSame(['a', 'b'], $tapped->all());
    }
}
