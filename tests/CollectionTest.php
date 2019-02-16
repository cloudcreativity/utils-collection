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

namespace CloudCreativity\Utils\Collection\Tests;

use ArrayObject;
use CloudCreativity\Utils\Collection\Collection;
use DateTime;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Class CollectionTest
 *
 * @package CloudCreativity\Utils\Collection
 */
class CollectionTest extends TestCase
{

    public function testConstruct()
    {
        $expected = ['foo', 'bar'];
        $collection = new Collection(...$expected);

        $this->assertSame($expected, $collection->all());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testConstruct
     */
    public function testTraversable(Collection $collection)
    {
        $this->assertSame($collection->all(), iterator_to_array($collection));
    }

    /**
     * @param Collection $collection
     * @depends testConstruct
     */
    public function testClear(Collection $collection)
    {
        $check = $collection->clear();

        $this->assertSame($collection, $check, 'Expecting `clear` to be chainable.');
        $this->assertEmpty($collection->all());
    }

    public function testStringify()
    {
        $collection = new Collection(...$expected = [1, 2, 3, 'foo', 'bar']);

        $this->assertEquals(json_encode($expected), (string) $collection);
    }

    public function testAdd()
    {
        $collection = new Collection('foo');
        $check = $collection->add('foo');

        $this->assertSame($collection, $check, 'Expecting `add` to be chainable.');
        $this->assertSame(['foo'], $collection->all());
        $this->assertSame(['foo'], $collection->add('foo')->all());
        $this->assertSame(['foo', 'bar'], $collection->add('bar')->all());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testAdd
     */
    public function testAddStrict(Collection $collection)
    {
        $expected = $collection->all();

        array_push($expected, 10, (string) 10);

        $collection->addStrict(10)
            ->addStrict(10)
            ->addStrict((string) 10);

        $this->assertSame($expected, $collection->all());
    }

    public function testAddMany()
    {
        $collection = new Collection('foo');
        $expected = ['foo', 'bar'];

        $check = $collection->addMany(['foo', 'bar', 'bar']);

        $this->assertSame($collection, $check, 'Expecting `addMany` to be chainable.');
        $this->assertSame($expected, $collection->all());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testAddMany
     */
    public function testAddManyIsStrict(Collection $collection)
    {
        $expected = $collection->all();
        array_push($expected, 10, (string) 10);
        $collection->addMany([10, (string) 10], true);

        $this->assertSame($expected, $collection->all());
    }

    public function testAny()
    {
        $collection = new Collection('foo', 10);

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

        $this->assertSame($collection->all(), $actual);
    }

    public function testChunk()
    {
        $collection = new Collection('a', 'b', 'c', 'd');

        $this->assertEquals([
            new Collection('a', 'b', 'c'),
            new Collection('d'),
        ], $collection->chunk(3)->all());

        $this->assertEquals([
            new Collection('a', 'b'),
            new Collection('c', 'd'),
        ], $collection->chunk(2)->all());

        $this->assertEquals([
            new Collection('a', 'b', 'c', 'd'),
        ], $collection->chunk(5)->all());
    }

    public function testCompact()
    {
        $collection = new Collection('foo', null, 'bar', null);
        $expected = new Collection('foo', 'bar');
        $check = $collection->compact();

        $this->assertNotSame($collection, $check);
        $this->assertEquals($expected, $check);
    }

    public function testDiff()
    {
        $collection = new Collection(1, 2, 3, 4, 5);
        $expected = new Collection(1, 3, 5);

        $this->assertEquals($expected, $actual = $collection->diff($diff = [2, 4, 6, 8]));
        $this->assertNotSame($collection, $actual);
        $this->assertEquals($expected, $collection->diff(new Collection(...$diff)));
    }

    public function testFilter()
    {
        $collection = new Collection('a', 10, 'b');
        $expected = new Collection('a', 'b');

        $actual = $collection->filter(function ($value) {
            return is_string($value);
        });

        $this->assertNotSame($collection, $actual);
        $this->assertEquals($expected, $actual);

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testFilter
     */
    public function testFilterReceivesKey(Collection $collection)
    {
        $actual = [];

        $collection->filter(function ($value, $key) use (&$actual) {
            $actual[$key] = $value;
            return true;
        });

        $this->assertSame($collection->all(), $actual);
    }

    public function testFind()
    {
        $collection = new Collection('a', 10, 'b');

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

        $this->assertSame($collection->all(), $actual);
    }

    public function testContains()
    {
        $collection = new Collection('a', '10');

        $this->assertTrue($collection->contains('a'));
        $this->assertTrue($collection->contains('a', 10));
        $this->assertFalse($collection->contains('b'));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testContains
     */
    public function testContainsUsingStrict(Collection $collection)
    {
        $this->assertTrue($collection->containsStrict('10'));
        $this->assertTrue($collection->containsStrict('10', 'a'));
        $this->assertFalse($collection->containsStrict(10));
        $this->assertFalse($collection->containsStrict('a', 10));
    }

    public function testCount()
    {
        $arr = ['a', 'b'];

        $this->assertSame(count($arr), count(new Collection(...$arr)));
    }

    public function testEach()
    {
        $expected = ['a', 'b', 'c'];
        $actual = [];

        Collection::create(...$expected)->each(function ($item, $key) use (&$actual) {
            $actual[$key] = $item;
        });

        $this->assertSame($expected, $actual);
    }

    public function testEachBreaks()
    {
        $actual = [];

        Collection::create(1, 2, 3, 4, 5)->each(function ($item) use (&$actual) {
            $actual[] = $item;
            return 3 > $item;
        });

        $this->assertSame([1, 2, 3], $actual);
    }

    public function testEquals()
    {
        $collection = new Collection('a', 'b');

        $this->assertTrue($collection->equals(clone $collection));

        $compare = clone $collection;
        $compare->push('b');

        $this->assertFalse($collection->equals($compare));

        $compare = new Collection(array_reverse($collection->all()));

        $this->assertFalse($collection->equals($compare));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testEquals
     */
    public function testEqualsStrict(Collection $collection)
    {
        $compare = clone $collection;
        $collection->push('10');
        $compare->push(10);

        $this->assertTrue($collection->equals($compare));
        $this->assertFalse($collection->equalsStrict($compare));
    }

    public function testEvery()
    {
        $collection = new Collection('a', 'b', 10);

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

        $this->assertSame($collection->all(), $actual);
    }

    public function testFill()
    {
        $collection = Collection::create('a', 'b')->fill(1, 'c');

        $this->assertSame(['a', 'b', 'c'], $collection->all());
        $this->assertSame($expected = ['a', 'b', 'c', 'c', 'c', 'c'], $collection->fill(3, 'c')->all());
        $this->assertSame($expected, $collection->fill(0, 'c')->all());
    }

    public function testFirst()
    {
        $collection = new Collection();

        $this->assertNull($collection->first());
        $collection->replace('a', 'b');
        $this->assertSame('a', $collection->first());
    }

    public function testFirstWithCallback()
    {
        $collection = new Collection(1, 2, 3, 4, 5);

        $this->assertSame(3, $collection->first(function ($value, $index) {
            return 3 === $value && 2 === $index;
        }));

        $this->assertNull($collection->first(function ($value) {
            return 5 < $value;
        }));
    }

    public function testImplode()
    {
        $collection = new Collection('a', 'b', 'c');

        $this->assertSame('abc', $collection->implode());
        $this->assertSame('a,b,c', $collection->implode(','));
    }

    public function testIndexOf()
    {
        $collection = new Collection('a', 'b', 'a');

        $this->assertSame(0, $collection->indexOf('a'));
        $this->assertSame(2, $collection->indexOf('a', 1));
        $this->assertFalse($collection->indexOf('c'));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testIndexOf
     */
    public function testIndexOfStrict(Collection $collection)
    {
        $collection->push('10');

        $this->assertSame(count($collection) - 1, $collection->indexOf(10));
        $this->assertFalse($collection->indexOfStrict(10, 0));
    }

    /**
     * @param Collection $collection
     * @depends testIndexOf
     */
    public function testIndexOfOutOfBounds(Collection $collection)
    {
        $this->expectException(OutOfBoundsException::class);
        $collection->indexOf('a', count($collection));
    }

    public function testInsertAt()
    {
        $collection = new Collection('a', 'a');

        $check = $collection->insertAt(1, 'b');

        $this->assertSame($collection, $check, 'Expecting `insertAt` to be chainable.');
        $this->assertSame(['a', 'b', 'a'], $collection->all());

        $collection->insertAt(3, 'b');
        $this->assertSame(['a', 'b', 'a', 'b'], $collection->all());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testInsertAt
     */
    public function testInsertAtOutOfBounds(Collection $collection)
    {
        $this->expectException(OutOfBoundsException::class);
        $collection->insertAt(count($collection) + 1, 'foobar');
    }

    public function testIntersect()
    {
        $collection = new Collection('a', 'b', 'c');
        $intersect = $collection->intersect($values = ['a', 'c', 'd']);

        $this->assertNotSame($collection, $intersect);
        $this->assertSame(['a', 'c'], $intersect->all());
        $this->assertSame(['a', 'c'], $collection->intersect(new Collection(...$values))->all());
    }

    public function testInvoke()
    {
        $collection = new Collection(
            $a = new DateTime(),
            $b = new DateTime('-1 day'),
            null
        );

        $expected = [$a->format('c'), $b->format('c'), null];
        $this->assertSame($expected, $collection->invoke('format', 'c')->all());
    }

    public function testInvokeInvalidMethod()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DateTime::foo');
        $collection = new Collection(new DateTime());
        $collection->invoke('foo');
    }

    public function testInvokeNonObject()
    {
        $this->expectException(RuntimeException::class);
        $collection = new Collection('foo');
        $collection->invoke('foo');
    }

    public function testItemAt()
    {
        $arr = ['a', 'b'];
        $collection = new Collection(...$arr);

        $this->assertSame($arr[0], $collection->itemAt(0));
        $this->assertSame($arr[1], $collection->itemAt(1));
        $this->assertNull($collection->itemAt(2));
    }

    public function testItemsAt()
    {
        $collection = new Collection('a', 'b', 'c');
        $expected = new Collection('a', 'c', null);
        $actual = $collection->itemsAt(0, 2, 3);

        $this->assertEquals($expected, $actual);
    }

    public function testJsonSerializable()
    {
        $expected = json_encode($arr = [1, 2, 3, 4, 5]);
        $actual = json_encode(Collection::create(...$arr));

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testLast()
    {
        $collection = new Collection();
        $this->assertNull($collection->last());
        $collection->push('a', 'b');
        $this->assertSame('b', $collection->last());
    }

    public function testLastWithCallback()
    {
        $collection = new Collection(1, 2, 3, 4, 5);

        $this->assertSame(2, $collection->last(function ($value, $index) {
            return $value < 3 && $index < 2;
        }));

        $this->assertNull($collection->last(function ($value) {
            return is_string($value);
        }));
    }

    public function testIsEmpty()
    {
        $collection = new Collection();

        $this->assertTrue($collection->isEmpty());
        $collection->push('a');
        $this->assertFalse($collection->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $collection = new Collection();

        $this->assertFalse($collection->isNotEmpty());
        $collection->push('a');
        $this->assertTrue($collection->isNotEmpty());
    }

    public function testUnique()
    {
        $collection = new Collection('a', 'b', 'a', null, 'b', 10, '10');

        $unique = $collection->unique();
        $this->assertNotSame($collection, $unique);

        $expected = ['a', 'b', null, 10];
        $this->assertSame($expected, $unique->all());

        $expected = ['a', 'b', null, 10, '10'];
        $this->assertSame($expected, $collection->uniqueStrict()->all());
    }

    public function testMap()
    {
        $collection = new Collection('a', 'b');
        $expected = ['aa0', 'bb1'];

        $mapped = $collection->map(function ($value, $key) {
            return $value . $value . $key;
        });

        $this->assertInstanceOf(Collection::class, $mapped);
        $this->assertNotSame($collection, $mapped);
        $this->assertSame($expected, $mapped->all());
    }

    public function testPad()
    {
        $collection = new Collection(...$original = [1, 2, 3, 4, 5]);
        $actual = $collection->pad(7);

        $this->assertSame([1, 2, 3, 4, 5, null, null], $actual->all());
        $this->assertNotSame($collection, $actual);
        $this->assertSame($original, $collection->all());
        $this->assertSame([1, 2, 3, 4, 5, 99, 99], $collection->pad(7, 99)->all(), 'pad with value');
        $this->assertSame($original, $collection->pad(2)->all(), 'pad length shorter than collection');
    }

    public function testPop()
    {
        $collection = new Collection('a', 'b');

        $this->assertSame('b', $collection->pop());
        $this->assertSame(['a'], $collection->all());

        $collection->pop();
        $this->assertEmpty($collection->all());
        $this->assertNull($collection->pop());
    }

    public function testReduce()
    {
        $callback = function ($previous, $value, $index) {
            return sprintf('%s-%s%d', $previous, $value, $index);
        };

        $expected = '-a0-b1';
        $collection = new Collection('a', 'b');
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
        $collection = new Collection('a', 'b', 10);

        $rejected = $collection->reject(function ($value) {
            return is_string($value);
        });

        $this->assertInstanceOf(Collection::class, $rejected);
        $this->assertNotSame($collection, $rejected);
        $this->assertSame([10], $rejected->all());

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

        $this->assertSame($collection->all(), $actual);
    }

    public function testRemove()
    {
        $collection = new Collection('a', 'b', 'a');

        $this->assertSame($collection, $collection->remove('a'));
        $this->assertSame(['b'], $collection->all());

        return $collection;
    }

    public function testRemoveStrict()
    {
        $collection = new Collection(10, '10');
        $other = $collection->copy();

        $other->removeStrict(10);
        $this->assertSame(['10'], $other->all());
    }

    public function testRemoveAt()
    {
        $collection = new Collection('a', 'b', 'c');

        $this->assertSame($collection, $collection->removeAt(1), 'Expecting `removeAt` to be chainable.');
        $this->assertSame(['a', 'c'], $collection->all());

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testRemoveAt
     */
    public function testRemoveAtOutOfBounds(Collection $collection)
    {
        $this->expectException(OutOfBoundsException::class);
        $collection->removeAt(count($collection));
    }

    public function testRemoveAtWithLength()
    {
        $collection = new Collection('a', 'b', 'b', 'b', 'c');

        $this->assertSame(['a', 'c'], $collection->copy()->removeAt(1, 3)->all());
        $this->assertSame(['a', 'b', 'c'], $collection->removeAt(2, 2)->all());
    }

    public function testRemoveMany()
    {
        $collection = new Collection('a', 'b', 'c', 10, 'b', '10');
        $other = $collection->copy();
        $check = $collection->removeMany(['b', '10']);

        $this->assertSame($collection, $check, 'Expecting `removeMany` to be chainable.');
        $this->assertSame(['a', 'c'], $collection->all());

        /** Strict */
        $this->assertSame(['a', 'c', 10], $other->removeMany(['b', '10'], true)->all());

        return $collection;
    }

    public function testReverse()
    {
        $expected = ['c', 'b', 'a'];
        $collection = new Collection('a', 'b', 'c');
        $reversed = $collection->reverse();

        $this->assertInstanceOf(Collection::class, $reversed);
        $this->assertNotSame($collection, $reversed);
        $this->assertSame($expected, $reversed->all());
    }

    public function testSearch()
    {
        $collection = new Collection('a', 10, 'a');

        /** Not strict */
        $this->assertSame(0, $collection->search('a'));
        $this->assertSame(1, $collection->search('10'));
        $this->assertFalse($collection->search('c'));

        /** Strict */
        $this->assertFalse($collection->searchStrict('10'));
        $collection->push('10');
        $this->assertSame(3, $collection->searchStrict('10'));

        return $collection;
    }

    public function testShift()
    {
        $collection = new Collection();

        $this->assertNull($collection->shift());
        $collection->pushMany(['a', 'b']);
        $this->assertSame('a', $collection->shift());
        $this->assertSame(['b'], $collection->all());
    }

    public function testSlice()
    {
        $original = ['a', 'b', 'c'];
        $collection = new Collection(...$original);
        $expected = ['b', 'c'];

        $sliced = $collection->slice(1);
        $this->assertInstanceOf(Collection::class, $sliced);
        $this->assertNotSame($collection, $sliced);
        $this->assertSame($expected, $sliced->all());
        $this->assertSame($original, $collection->all());

        return $collection;
    }

    public function testSliceWithEnd()
    {
        $collection = new Collection('a', 'b', 'c', 'd');

        $this->assertSame(['b', 'c'], $collection->slice(1, 2)->all());
    }

    public function testSort()
    {
        $original = [3, 1, 2];
        $expected = [1, 2, 3];
        $collection = new Collection(...$original);

        $sorted = $collection->sort(function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : +1;
        });

        $this->assertInstanceOf(Collection::class, $sorted);
        $this->assertNotSame($collection, $sorted);
        $this->assertSame($expected, $sorted->all());
        $this->assertSame($original, $collection->all());
    }

    public function testSync()
    {
        $collection = new Collection(1, 2, 3, 4);
        $callback = function ($item) {
            return is_int($item) ? $item * 10 : 'foo';
        };

        $actual = $collection->sync(4, $callback);
        $this->assertSame([10, 20, 30, 40], $actual->all());
        $this->assertNotSame($collection, $actual);
        $this->assertSame([10, 20], $collection->sync(2, $callback)->all());

        /** Overflow with no callable */
        $extra = new Collection();
        $actual = $collection->sync(2, null, function ($item) use ($extra) {
            $extra->push($item);
        });

        $this->assertSame([1, 2], $actual->all());
        $this->assertSame([3, 4], $extra->all());

        /** Callable and overflow */
        $extra = new Collection();
        $actual = $collection->sync(2, $callback, function ($item) use ($extra) {
            $extra->push($item);
        });

        $this->assertSame([10, 20], $actual->all());
        $this->assertSame([3, 4], $extra->all());

        /** Size is less than current length */
        $this->assertSame([10, 20, 30, 40, 'foo', 'foo'], $collection->sync(6, $callback, function () {
            $this->fail('Overflow callback should not be invoked when there is no overflow.');
        })->all());
    }

    public function testTake()
    {
        $collection = new Collection(...$original = [1, 2, 3, 4, 5]);

        $this->assertSame([1, 2], $collection->take(2)->all());
        $this->assertSame([4, 5], $collection->take(-2)->all());
        $this->assertSame($original, $collection->all());
    }

    public function testTap()
    {
        $tapped = null;

        $actual = Collection::create(...$expected = [1, 2, 3, 4, 5])
            ->tap(function (Collection $collection) use (&$tapped) {
                $tapped = $collection->push(6);
            })
            ->all();

        $this->assertSame($expected, $actual, 'Original collection is not modified');
        $this->assertSame([1, 2, 3, 4, 5, 6], $tapped->all());
    }

    public function testUnshift()
    {
        $collection = new Collection('a', 'b');

        $this->assertSame($collection, $collection->unshift('c', 'd'));
        $this->assertSame(['c', 'd', 'a', 'b'], $collection->all());
    }

    public function testUnshiftMany()
    {
        $collection = new Collection('a', 'b');
        $expected = ['c', 'd', 'e', 'a', 'b'];

        $this->assertSame($collection, $collection->unshiftMany(['c', 'd', 'e']));
        $this->assertSame($expected, $collection->all());
    }

    public function testUnshiftObjects()
    {
        $a = (object) ['foo' => 'bar'];
        $b = (object) ['baz' => 'bat'];
        $c = (object) ['foobar' => 'bazbat'];

        $collection = Collection::create($a)->unshiftObjects($b, null, $c);
        $this->assertSame([$b, $c, $a], $collection->all());
    }

    public function testWithout()
    {
        $original = ['a', 'b', 'a', 'b', 'c'];
        $collection = new Collection(...$original);
        $expected = ['a', 'a'];
        $without = $collection->without('b', 'c');

        $this->assertInstanceOf(Collection::class, $without);
        $this->assertNotSame($collection, $without);
        $this->assertSame($expected, $without->all());
        $this->assertSame($original, $collection->all());
    }

    public function testWithoutValueNotContained()
    {
        $expected = ['a', 'b'];
        $collection = new Collection(...$expected);

        $this->assertEquals($collection->all(), $collection->without('c')->all());
    }

    public function testWithoutStrict()
    {
        $collection = new Collection(1, 2, 3);

        $this->assertSame([1, 2, 3], $collection->withoutStrict('2')->all());
        $this->assertSame([1, 3], $actual = $collection->withoutStrict(2)->all());
        $this->assertNotSame($collection, $actual);
        $this->assertSame([1, 2, 3], $collection->all());
    }

    public function testCast()
    {
        $expected = ['a', 'b', 'c'];
        $collection = Collection::cast($expected);

        $this->assertEquals(new Collection(...$expected), $collection);
    }

    public function testCastCollection()
    {
        $expected = new Collection('a', 'b');

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

        $this->assertEquals(new Collection('a', 'b', 'c'), $collection);
    }

    public function testCastTraversable()
    {
        $obj = new ArrayObject(['a', 'b', 'c']);
        $collection = Collection::cast($obj);

        $this->assertEquals(new Collection('a', 'b', 'c'), $collection);
    }

    public function testCastStdClass()
    {
        $this->expectException(InvalidArgumentException::class);
        Collection::cast((object) ['foo' => 'bar']);
    }

    public function testCastStandardIterator()
    {
        $iterator = new DateTimeIterator(new DateTime());
        $collection = Collection::cast($iterator);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame($iterator->all(), $collection->all());
    }

    public function testCreate()
    {
        $this->assertEquals(new Collection('a', 'b'), Collection::create('a', 'b'));
    }

}
