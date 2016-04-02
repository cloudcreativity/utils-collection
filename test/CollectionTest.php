<?php

namespace CloudCreativity\Utils\Collection;

use ArrayObject;
use OutOfBoundsException;
use stdClass;

class CollectionTest extends TestCase
{

    public function testConstruct()
    {
        $expected = ['foo', 'bar'];
        $collection = new Collection($expected);

        $this->assertSame($expected, $collection->toArray());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testConstruct
     */
    public function testTraversable(Collection $collection)
    {
        $this->assertSame($collection->toArray(), iterator_to_array($collection));
    }

    /**
     * @param Collection $collection
     * @depends testConstruct
     */
    public function testClear(Collection $collection)
    {
        $check = $collection->clear();

        $this->assertSame($collection, $check, 'Expecting `clear` to be chainable.');
        $this->assertEmpty($collection->toArray());
    }

    public function testAdd()
    {
        $expected = ['foo'];

        $collection = new Collection($expected);
        $check = $collection->add('foo');

        $this->assertSame($collection, $check, 'Expecting `add` to be chainable.');
        $this->assertSame($expected, $collection->toArray());
        $this->assertSame($expected, $collection->add('foo')->toArray());

        $expected[] = 'bar';
        $this->assertSame($expected, $collection->add('bar')->toArray());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testAdd
     */
    public function testAddIsStrict(Collection $collection)
    {
        $expected = $collection->toArray();

        array_push($expected, 10, (string) 10);

        $collection->add(10, true)
            ->add(10, true)
            ->add((string) 10, true);

        $this->assertSame($expected, $collection->toArray());
    }

    public function testAddMany()
    {
        $collection = new Collection(['foo']);
        $expected = ['foo', 'bar'];

        $check = $collection->addMany(['foo', 'bar', 'bar']);

        $this->assertSame($collection, $check, 'Expecting `addMany` to be chainable.');
        $this->assertSame($expected, $collection->toArray());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testAddMany
     */
    public function testAddManyIsStrict(Collection $collection)
    {
        $expected = $collection->toArray();
        array_push($expected, 10, (string) 10);
        $collection->addMany([10, (string) 10], true);

        $this->assertSame($expected, $collection->toArray());
    }

    public function testAny()
    {
        $collection = new Collection(array('foo', 10));

        $this->assertTrue($collection->any(function ($value) {
            return is_int($value);
        }));

        $this->assertTrue($collection->any(function ($value) {
            return is_string($value);
        }));

        $this->assertFalse($collection->any(function ($value) {
            return is_bool($value);
        }));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testAny
     */
    public function testAnyReceivesIndex($collection)
    {
        $actual = [];

        $collection->any(function ($value, $index) use (&$actual) {
            $actual[$index] = $value;
            return false;
        });

        $this->assertSame($collection->toArray(), $actual);
    }

    public function testChunk()
    {
        $collection = new Collection(['a', 'b', 'c', 'd']);

        $this->assertEquals([
            new Collection(['a', 'b', 'c']),
            new Collection(['d']),
        ], $collection->chunk(3));

        $this->assertEquals([
            new Collection(['a', 'b']),
            new Collection(['c', 'd']),
        ], $collection->chunk(2));

        $this->assertEquals([
            new Collection(['a', 'b', 'c', 'd']),
        ], $collection->chunk(5));
    }

    public function testCompact()
    {
        $collection = new Collection(['foo', null, 'bar', null]);
        $expected = new Collection(['foo', 'bar']);
        $check = $collection->compact();

        $this->assertNotSame($collection, $check);
        $this->assertEquals($expected, $check);
    }

    public function testFilter()
    {
        $collection = new Collection(['a', 10, 'b']);
        $expected = new Collection(['a', 'b']);

        $actual = $collection->filter(function ($value) {
            return is_string($value);
        });

        $this->assertNotSame($collection, $actual);
        $this->assertEquals($expected, $actual);

        return $collection;
    }

    /**
    *  @param Collection $collection
     * @depends testFilter
     */
    public function testFilterReceivesKey(Collection $collection)
    {
        $actual = [];

        $collection->filter(function ($value, $key) use (&$actual) {
            $actual[$key] = $value;
            return true;
        });

        $this->assertSame($collection->toArray(), $actual);
    }

    public function testFind()
    {
        $collection = new Collection(['a', 10, 'b']);

        $found = $collection->find(function ($value) {
            return is_int($value);
        });

        $this->assertSame(10, $found);

        $notFound = $collection->find(function ($value) {
            return false;
        });

        $this->assertNull($notFound, 'Expecting null to be returned if nothing is found.');

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testFind
     */
    public function testFindReceivesKey(Collection $collection)
    {
        $actual = [];

        $collection->find(function ($value, $key) use (&$actual) {
            $actual[$key] = $value;
            return false;
        });

        $this->assertSame($collection->toArray(), $actual);
    }

    public function testContains()
    {
        $collection = new Collection(['a', '10']);

        $this->assertTrue($collection->contains('a'));
        $this->assertFalse($collection->contains('b'));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testContains
     */
    public function testContainsUsingStrict(Collection $collection)
    {
        $this->assertTrue($collection->contains(10));
        $this->assertFalse($collection->contains(10, true));
    }

    public function testCount()
    {
        $arr = ['a', 'b'];

        $this->assertSame(count($arr), count(new Collection($arr)));
    }

    public function testEquals()
    {
        $collection = new Collection(['a', 'b']);

        $this->assertTrue($collection->equals(clone $collection));

        $compare = clone $collection;
        $compare->push('b');

        $this->assertFalse($collection->equals($compare));

        $compare = new Collection(array_reverse($collection->toArray()));

        $this->assertFalse($collection->equals($compare));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testEquals
     */
    public function testEqualsWithStrict(Collection $collection)
    {
        $compare = clone $collection;
        $collection->push('10');
        $compare->push(10);

        $this->assertTrue($collection->equals($compare));
        $this->assertFalse($collection->equals($compare, true));
    }

    public function testEvery()
    {
        $collection = new Collection(['a', 'b', 10]);

        $this->assertTrue($collection->every(function ($value) {
            return is_scalar($value);
        }));

        $this->assertFalse($collection->every(function ($value) {
            return is_string($value);
        }));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testEvery
     */
    public function testEveryReceivesKey(Collection $collection)
    {
        $actual = [];

        $collection->every(function ($value, $key) use (&$actual) {
            $actual[$key] = $value;
            return true;
        });

        $this->assertSame($collection->toArray(), $actual);
    }

    public function testFirst()
    {
        $collection = new Collection();

        $this->assertNull($collection->first());
        $collection->replace(['a', 'b']);
        $this->assertSame('a', $collection->first());
    }

    public function testIndexOf()
    {
        $collection = new Collection(['a', 'b', 'a']);

        $this->assertSame(0, $collection->indexOf('a'));
        $this->assertSame(2, $collection->indexOf('a', 1));
        $this->assertFalse($collection->indexOf('c'));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testIndexOf
     */
    public function testIndexOfUsesStrict(Collection $collection)
    {
        $collection->push('10');

        $this->assertSame(count($collection) - 1, $collection->indexOf(10));
        $this->assertFalse($collection->indexOf(10, 0, true));
    }

    /**
     * @param Collection $collection
     * @depends testIndexOf
     */
    public function testIndexOfOutOfBounds(Collection $collection)
    {
        $this->setExpectedException(OutOfBoundsException::class);
        $collection->indexOf('a', count($collection));
    }

    public function testInsertAt()
    {
        $collection = new Collection(['a', 'a']);

        $check = $collection->insertAt(1, 'b');

        $this->assertSame($collection, $check, 'Expecting `insertAt` to be chainable.');
        $this->assertSame(['a', 'b', 'a'], $collection->toArray());

        $collection->insertAt(3, 'b');
        $this->assertSame(['a', 'b', 'a', 'b'], $collection->toArray());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testInsertAt
     */
    public function testInsertAtOutOfBounds(Collection $collection)
    {
        $this->setExpectedException(OutOfBoundsException::class);
        $collection->insertAt(count($collection) + 1, 'foobar');
    }

    public function testItemAt()
    {
        $arr = ['a', 'b'];
        $collection = new Collection($arr);

        $this->assertSame($arr[0], $collection->itemAt(0));
        $this->assertSame($arr[1], $collection->itemAt(1));
        $this->assertNull($collection->itemAt(2));
    }

    public function testItemsAt()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $expected = new Collection(['a', 'c', null]);
        $actual = $collection->itemsAt([0, 2, 3]);

        $this->assertEquals($expected, $actual);
    }

    public function testLast()
    {
        $collection = new Collection();
        $this->assertNull($collection->last());
        $collection->pushMany(['a', 'b']);
        $this->assertSame('b', $collection->last());
    }

    public function testIsEmpty()
    {
        $collection = new Collection();

        $this->assertTrue($collection->isEmpty());
        $collection->push('a');
        $this->assertFalse($collection->isEmpty());
    }

    public function testUnique()
    {
        $collection = new Collection(['a', 'b', 'a', null, 'b', 10, '10']);

        $unique = $collection->unique();
        $this->assertNotSame($collection, $unique);

        /** Without strict */
        $expected = ['a', 'b', null, 10];
        $this->assertSame($expected, $unique->toArray());

        /** With strict */
        $expected = ['a', 'b', null, 10, '10'];
        $this->assertSame($expected, $collection->unique(true)->toArray());
    }

    public function testMap()
    {
        $collection = new Collection(['a', 'b']);
        $expected = ['aa0', 'bb1'];

        $mapped = $collection->map(function ($value, $key) {
            return $value . $value . $key;
        });

        $this->assertInstanceOf(Collection::class, $mapped);
        $this->assertNotSame($collection, $mapped);
        $this->assertSame($expected, $mapped->toArray());
    }

    public function testPop()
    {
        $collection = new Collection(['a', 'b']);

        $this->assertSame('b', $collection->pop());
        $this->assertSame(['a'], $collection->toArray());

        $collection->pop();
        $this->assertEmpty($collection->toArray());
        $this->assertNull($collection->pop());
    }

    public function testReduce()
    {
        $callback = function ($previous, $value, $index) {
            return sprintf('%s-%s%d', $previous, $value, $index);
        };

        $expected = '-a0-b1';
        $collection = new Collection(['a', 'b']);
        $actual = $collection->reduce($callback);

        $this->assertSame($expected, $actual);
        $this->assertSame('foo' . $expected, $collection->reduce($callback, 'foo'));
    }

    public function testReduceEmpty()
    {
        $callback = function ($value) {
            return 'boo!';
        };

        $collection = new Collection();
        $this->assertNull($collection->reduce($callback));
        $this->assertSame('foo', $collection->reduce($callback, 'foo'));
    }

    public function testReject()
    {
        $collection = new Collection(['a', 'b', 10]);

        $rejected = $collection->reject(function ($value) {
            return is_string($value);
        });

        $this->assertInstanceOf(Collection::class, $rejected);
        $this->assertNotSame($collection, $rejected);
        $this->assertSame([10], $rejected->toArray());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testReject
     */
    public function testRejectReceivesIndex(Collection $collection)
    {
        $actual = [];

        $collection->reject(function ($value, $key) use (&$actual) {
            $actual[$key] = $value;
        });

        $this->assertSame($collection->toArray(), $actual);
    }

    public function testRemove()
    {
        $collection = new Collection(['a', 'b', 'a']);

        $this->assertSame($collection, $collection->remove('a'));
        $this->assertSame(['b'], $collection->toArray());

        return $collection;
    }

    public function testRemoveUsingStrict()
    {
        $collection = new Collection([10, '10']);
        $other = $collection->copy();

        $collection->remove(10);
        $this->assertEmpty($collection->toArray());

        $other->remove(10, true);
        $this->assertSame(['10'], $other->toArray());
    }

    public function testRemoveAt()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $expected = ['a', 'c'];

        $this->assertSame($collection, $collection->removeAt(1), 'Expecting `removeAt` to be chainable.');
        $this->assertSame($expected, $collection->toArray());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testRemoveAt
     */
    public function testRemoveAtOutOfBounds(Collection $collection)
    {
        $this->setExpectedException(OutOfBoundsException::class);
        $collection->removeAt(count($collection));
    }

    public function testRemoveAtWithLength()
    {
        $collection = new Collection(['a', 'b', 'b', 'b', 'c']);

        $this->assertSame(['a', 'c'], $collection->copy()->removeAt(1, 3)->toArray());
        $this->assertSame(['a', 'b', 'c'], $collection->removeAt(2, 2)->toArray());
    }

    public function testRemoveMany()
    {
        $collection = new Collection(['a', 'b', 'c', 10, 'b', '10']);
        $other = $collection->copy();
        $check = $collection->removeMany(['b', '10']);

        $this->assertSame($collection, $check, 'Expecting `removeMany` to be chainable.');
        $this->assertSame(['a', 'c'], $collection->toArray());

        /** Strict */
        $this->assertSame(['a', 'c', 10], $other->removeMany(['b', '10'], true)->toArray());

        return $collection;
    }

    public function testReverse()
    {
        $expected = ['c', 'b', 'a'];
        $collection = new Collection(['a', 'b', 'c']);
        $reversed = $collection->reverse();

        $this->assertInstanceOf(Collection::class, $reversed);
        $this->assertNotSame($collection, $reversed);
        $this->assertSame($expected, $reversed->toArray());
    }

    public function testSearch()
    {
        $collection = new Collection(['a', 10, 'a']);

        /** Not strict */
        $this->assertSame(0, $collection->search('a'));
        $this->assertSame(1, $collection->search('10'));
        $this->assertFalse($collection->search('c'));

        /** Strict */
        $collection->push('10');
        $this->assertSame(3, $collection->search('10', true));

        return $collection;
    }

    public function testShift()
    {
        $collection = new Collection();

        $this->assertNull($collection->shift());
        $collection->pushMany(['a', 'b']);
        $this->assertSame('a', $collection->shift());
        $this->assertSame(['b'], $collection->toArray());
    }

    public function testSlice()
    {
        $original = ['a', 'b', 'c'];
        $collection = new Collection($original);
        $expected = ['b', 'c'];

        $sliced = $collection->slice(1);
        $this->assertInstanceOf(Collection::class, $sliced);
        $this->assertNotSame($collection, $sliced);
        $this->assertSame($expected, $sliced->toArray());
        $this->assertSame($original, $collection->toArray());

        return $collection;
    }

    public function testSliceWithEnd()
    {
        $collection = new Collection(['a', 'b', 'c', 'd']);

        $this->assertSame(['b', 'c'], $collection->slice(1, 2)->toArray());
    }

    public function testSort()
    {
        $original = [3, 1, 2];
        $expected = [1, 2, 3];
        $collection = new Collection($original);

        $sorted = $collection->sort(function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : +1;
        });

        $this->assertInstanceOf(Collection::class, $sorted);
        $this->assertNotSame($collection, $sorted);
        $this->assertSame($expected, $sorted->toArray());
        $this->assertSame($original, $collection->toArray());
    }

    public function testSync()
    {
        $collection = new Collection([1, 2, 3, 4]);
        $callback = function ($item) {
            return is_int($item) ? $item * 10 : 'foo';
        };

        $actual = $collection->sync(4, $callback);
        $this->assertEquals(new Collection([10, 20, 30, 40]), $actual);
        $this->assertNotSame($collection, $actual);

        $this->assertEquals(new Collection([10, 20]), $collection->sync(2, $callback));

        /** Overflow with no callable */
        $extra = new Collection();
        $actual = $collection->sync(2, null, function ($item) use ($extra) {
            $extra->push($item);
        });

        $this->assertEquals(new Collection([1, 2]), $actual);
        $this->assertEquals(new Collection([3, 4]), $extra);

        /** Callable and overflow */
        $extra = new Collection();
        $actual = $collection->sync(2, $callback, function ($item) use ($extra) {
            $extra->push($item);
        });

        $this->assertEquals(new Collection([10, 20]), $actual);
        $this->assertEquals(new Collection([3, 4]), $extra);

        /** Size is less than current length */
        $this->assertEquals(new Collection([
            10, 20, 30, 40, 'foo', 'foo'
        ]), $collection->sync(6, $callback, function () {
            $this->fail('Overflow callback should not be invoked when there is no overflow.');
        }));
    }

    public function testUnshift()
    {
        $collection = new Collection(['a', 'b']);

        $this->assertSame($collection, $collection->unshift('c'));
        $this->assertSame(['c', 'a', 'b'], $collection->toArray());
    }

    public function testUnshiftMany()
    {
        $collection = new Collection(['a', 'b']);
        $expected = ['c', 'd', 'e', 'a', 'b'];

        $this->assertSame($collection, $collection->unshiftMany(['c', 'd', 'e']));
        $this->assertSame($expected, $collection->toArray());
    }

    public function testWithout()
    {
        $original = ['a', 'b', 'a', 'b'];
        $collection = new Collection($original);
        $expected = ['a', 'a'];
        $without = $collection->without('b');

        $this->assertInstanceOf(Collection::class, $without);
        $this->assertNotSame($collection, $without);
        $this->assertSame($expected, $without->toArray());
        $this->assertSame($original, $collection->toArray());
    }

    public function testWithoutValueNotContained()
    {
        $expected = ['a', 'b'];
        $collection = new Collection($expected);

        $this->assertEquals($collection->toArray(), $collection->without('c')->toArray());
    }

    public function testWithoutUsingStrict()
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertSame([1, 3], $collection->without('2')->toArray());
        $this->assertSame([1, 2, 3], $collection->without('2', true)->toArray());
        $this->assertSame([1, 3], $collection->without(2, true)->toArray());
    }

    public function testCast()
    {
        $expected = ['a', 'b', 'c'];
        $collection = Collection::cast($expected);

        $this->assertEquals(new Collection($expected), $collection);
    }

    public function testCastCollection()
    {
        $expected = new Collection(['a', 'b']);

        $this->assertSame($expected, Collection::cast($expected));
    }

    public function testCastNonListArray()
    {
        $arr = [
            'foo' => 'a',
            'bar' => 'b',
            'baz' => 'c',
        ];

        $collection = Collection::cast($arr);

        $this->assertEquals(new Collection(['a', 'b', 'c']), $collection);
    }

    public function testCastStdClass()
    {
        $obj = new stdClass;
        $obj->foo = 'a';
        $obj->bar = 'b';
        $obj->baz = 'c';

        $collection = Collection::cast($obj);

        $this->assertEquals(new Collection(['a', 'b', 'c']), $collection);
    }

    public function testCastTraversable()
    {
        $obj = new ArrayObject(['a', 'b', 'c']);
        $collection = Collection::cast($obj);

        $this->assertEquals(new Collection(['a', 'b', 'c']), $collection);
    }

}
