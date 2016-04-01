<?php

namespace CloudCreativity\Utils\Collection;

use OutOfBoundsException;

class StandardIteratorTest extends TestCase
{

    /**
     * @param TestIterator
     */
    private $iterator;

    protected function setUp()
    {
        $this->iterator = new TestIterator(['a', 'b', 'c']);
    }

    public function testGetAll()
    {
        $this->assertSame(['a', 'b', 'c'], $this->iterator->getAll());
    }

    public function testFirst()
    {
        $this->assertSame('a', $this->iterator->first());

        $this->setExpectedException(OutOfBoundsException::class);
        (new TestIterator())->first();
    }

    public function testLast()
    {
        $this->assertSame('c', $this->iterator->last());

        $this->setExpectedException(OutOfBoundsException::class);
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

        $this->assertFalse($this->iterator->every(function ($item)) {
            return in_array($item, ['a', 'b']);
        });
    }

    public function testAny()
    {
        $this->assertTrue($this->iterator->any(function ($item)) {
            return 'c' === $item;
        });

        $this->assertFalse($this->iterator->any(function ($item, $index)) {
            return 3 < $index;
        });
    }

    public function testIsEmpty()
    {
        $this->assertFalse($this->iterator->isEmpty());
        $this->assertTrue((new TestIterator())->isEmpty());
    }

    public function testCount()
    {
        $this->assertSame(3, $this->iterator->count());
    }
}
