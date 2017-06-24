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

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;
use RuntimeException;
use Traversable;

/**
 * Class Collection
 *
 * @package CloudCreativity\Utils\Collection
 */
class Collection implements IteratorAggregate, Countable
{

    /**
     * @var array
     */
    private $stack;

    /**
     * Fluent constructor.
     *
     * @param array ...$items
     * @return Collection
     */
    public static function create(...$items)
    {
        return new self(...$items);
    }

    /**
     * Cast the supplied items to a Collection object.
     *
     * If `$collection` is already a Collection, then the same object
     * will be returned. If it is an array or Traversable object, it will be
     * converted to a Collection object.
     *
     * @param self|array|Traversable $items
     * @return Collection
     */
    public static function cast($items)
    {
        if ($items instanceof static) {
            return $items;
        } elseif (is_array($items)) {
            return new self(...array_values($items));
        } elseif ($items instanceof Traversable) {
            return new self(...$items);
        }

        throw new InvalidArgumentException('Cannot cast provided value to a collection.');
    }

    /**
     * Collection constructor.
     *
     * @param array ...$items
     */
    public function __construct(...$items)
    {
        $this->stack = $items;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->stack);
    }

    /**
     * Adds the item if it is not already in the collection.
     *
     * @param array ...$items
     * @return $this
     */
    public function add(...$items)
    {
        foreach ($items as $item) {
            if (!in_array($item, $this->stack)) {
                $this->push($item);
            }
        }

        return $this;
    }

    /**
     * @param array ...$items
     * @return $this
     */
    public function addStrict(...$items)
    {
        foreach ($items as $item) {
            if (!in_array($item, $this->stack, true)) {
                $this->push($item);
            }
        }

        return $this;
    }

    /**
     * Adds any items that are not already in the collection.
     *
     * @param array|Traversable $items
     * @param bool $strict
     * @return $this
     * @deprecated use `add` or `addStrict`
     */
    public function addMany($items, $strict = false)
    {
        if ($strict) {
            $this->addStrict(...$items);
        } else {
            $this->add(...$items);
        }

        return $this;
    }

    /**
     * Add an item if it is an object and is not already in the collection.
     *
     * @param mixed $object
     * @return $this
     * @deprecated use `addObjects`
     */
    public function addObject($object)
    {
        $this->addObjects($object);

        return $this;
    }

    /**
     * Adds any items that are an object and not already in the collection.
     *
     * @param array ...$objects
     * @return $this
     */
    public function addObjects(...$objects)
    {
        foreach ($objects as $object) {
            if (is_object($object)) {
                $this->addStrict($object);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->stack;
    }

    /**
     * Returns true if the supplied callback returns true for any item in the collection.
     *
     * The supplied callback should have the following signature:
     *
     * `function($item, $index)`
     *
     * Where `$item` is the current item in the iteration and `$index` is the
     * current index in the interation.
     *
     * @param callable $callback
     *      The supplied callback
     * @return bool
     *      Whether the callback returns true for any item in the collection.
     */
    public function any(callable $callback)
    {
        foreach ($this as $key => $value) {
            if (true == call_user_func($callback, $value, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $size
     * @return Collection[]
     * @todo should this return a collection of collections?
     */
    public function chunk($size)
    {
        $ret = [];

        foreach (array_chunk($this->stack, $size, false) as $chunk) {
            $ret[] = new static(...$chunk);
        }

        return $ret;
    }

    /**
     * Empty the collection.
     *
     * @return $this
     */
    public function clear()
    {
        $this->stack = [];

        return $this;
    }

    /**
     * Returns a copy of the collection with all null items removed.
     *
     * @return Collection
     */
    public function compact()
    {
        return $this->filter(function ($value) {
            return !is_null($value);
        });
    }

    /**
     * Returns true if all the supplied items are found within the collection.
     *
     * @param array ...$items
     * @return boolean
     */
    public function contains(...$items)
    {
        return self::create(...$items)->every(function ($item) {
            return in_array($item, $this->stack);
        });
    }

    /**
     * Returns true if all the supplied items are found within the collection, using strict comparison.
     *
     * @param array ...$items
     * @return boolean
     */
    public function containsStrict(...$items)
    {
        return self::create(...$items)->every(function ($item) {
            return in_array($item, $this->stack, true);
        });
    }

    /**
     * Get a copy (clone) of this collection.
     *
     * Copy returns a clone of this collection without cloning the contents
     * of the collection - i.e. it is a shallow clone.
     *
     * To do a deep clone of the collection, use `replicate()`.
     *
     * @return Collection
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * How many items are in the collection?
     *
     * @return int
     */
    public function count()
    {
        return count($this->stack);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this as $key => $item) {
            if (false === $callback($item, $key)) {
                break;
            }
        }

        return $this;
    }

    /**
     * Returns true if the supplied collection is equal to the current collection.
     *
     * This method returns true if the collection within `$compare` is the same
     * as the collection within this collection. The class of `$this` and
     * `$object` is not factored in.
     *
     * @param array|Traversable $compare
     * @return bool
     */
    public function equals($compare)
    {
        return $this->stack == self::cast($compare)->stack;
    }

    /**
     * Returns true if the supplied collection is equal to the current collection, using strict comparison.
     *
     * This method returns true if the collection within `$compare` is the same
     * as the collection within this collection. The class of `$this` and
     * `$object` is not factored in.
     *
     * @param $compare
     * @return bool
     */
    public function equalsStrict($compare)
    {
        return $this->stack === self::cast($compare)->stack;
    }

    /**
     * Returns true if the callback returns true for every item in the collection.
     *
     * The callback should have the following signature:
     *
     * `function($item, $index)`
     *
     * Where `$item` is the current item in the iteration and `$index` is the
     * current index in the iteration.
     *
     * @param callable $callback
     * @return bool
     *    True if the callback returns true for every item.
     */
    public function every(callable $callback)
    {
        foreach ($this as $key => $value) {

            if (true != call_user_func($callback, $value, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a copy of the collection that contains only items for which the callback returns true.
     *
     * The callback should have the following signature:
     *
     * `function($item, $index)`
     *
     * Where `$item` is the current item in the iteration and `$index` is the
     * current index in the iteration.
     *
     * @param callable $callback
     * @return Collection
     */
    public function filter(callable $callback)
    {
        $collection = new static();

        foreach ($this as $key => $value) {
            if (false != call_user_func($callback, $value, $key)) {
                $collection->stack[] = $value;
            }
        }

        return $collection;
    }

    /**
     * Returns the first item in the collection for which the callback returns true.
     *
     * If the callback does not return true for any item in the collection, a
     * `null` value will be returned instead. The callback should have the
     * following signature:
     *
     * `function($item, $index)`
     *
     * Where `$item` is the current item in the iteration and `$index` is the
     * current index in the iteration.
     *
     * @param callable $callback
     * @return mixed
     *    The matched item or null if no item matches.
     */
    public function find(callable $callback)
    {
        foreach ($this as $key => $value) {
            if (true == call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get the first item in the collection.
     *
     * @return mixed
     *    The first item in the collection or null if the collection is empty.
     */
    public function first()
    {
        return count($this->stack) == 0 ? null : $this->stack[0];
    }

    /**
     * Returns the index of the first matching item in the collection.
     *
     * This method returns the index of the first item in the collection that
     * matches the supplied item. If no matches are found, a `false` boolean
     * will be returned.
     *
     * If `$startAt` is provided, the search for a matching item will begin at
     * the `$startAt` index in the array.
     *
     * @param mixed $item
     *     The item being searched against.
     * @param integer $startAt
     *     The index in the collection to start searching from.
     * @return integer|false
     *     The integer index in the array, or false if no matches.
     * @throws OutOfBoundsException
     *     If `$startAt` is out of bounds.
     */
    public function indexOf($item, $startAt = 0)
    {
        if (count($this) <= $startAt) {
            throw new OutOfBoundsException(sprintf('Index "%s" is out of bounds.', $startAt));
        }

        for ($i = $startAt; $i < count($this->stack); $i++) {
            if ($item == $this->stack[$i]) {
                return $i;
            }
        }

        return false;
    }

    /**
     * Returns the index of the first matching item in the collection, using strict comparison.
     *
     * This method returns the index of the first item in the collection that
     * matches the supplied item. If no matches are found, a `false` boolean
     * will be returned.
     *
     * If `$startAt` is provided, the search for a matching item will begin at
     * the `$startAt` index in the array.
     *
     * @param mixed $item
     *     The item being searched against.
     * @param integer $startAt
     *     The index in the collection to start searching from.
     * @return integer|false
     *     The integer index in the array, or false if no matches.
     * @throws OutOfBoundsException
     *     If `$startAt` is out of bounds.
     */
    public function indexOfStrict($item, $startAt = 0)
    {
        if (count($this) <= $startAt) {
            throw new OutOfBoundsException(sprintf('Index "%s" is out of bounds.', $startAt));
        }

        for ($i = $startAt; $i < count($this->stack); $i++) {
            if ($item === $this->stack[$i]) {
                return $i;
            }
        }

        return false;
    }

    /**
     * Inserts a value at the provided index.
     *
     * This method will insert the provided item at the supplied index. `$index`
     * can be one more than the current final index (if this is the case, the item
     * will be added to the end of the collection). If higher, an error will be
     * thrown as the index will be out of bounds. If the collection is empty,
     * then the error will be thrown if `$index` is more than zero.
     *
     * @param integer $index
     *    The index that the item should be inserted at.
     * @param mixed $item
     *    The item to be inserted.
     * @return $this
     * @throws OutOfBoundsException
     *    if the index is exceeds adding the item to the end of the collection.
     */
    public function insertAt($index, $item)
    {
        if (!is_int($index) || $index < 0) {
            throw new InvalidArgumentException('Expecting a positive integer.');
        } elseif (count($this) < $index) {
            throw new OutOfBoundsException(sprintf('Index "%s" is out of bounds.', $index));
        }

        array_splice($this->stack, $index, 0, [$item]);

        return $this;
    }

    /**
     * Return a new collection with the result of invoking the specified method on each object in the collection.
     *
     * If this method encounters an items in the collection that are not objects,
     * it will map those items to null. Otherwise, for each object in the
     * collection the specified method will be invoked with the provided
     * argument(s).
     *
     * @param $method
     * @param array ...$args
     * @return Collection
     */
    public function invoke($method, ...$args)
    {
        return $this->map(function ($item) use ($method, $args) {
            if (is_null($item)) {
                return null;
            }

            if (!is_object($item)) {
                throw new RuntimeException('Collection contains a non-object.');
            }

            $callable = [$item, $method];

            if (!is_callable($callable)) {
                throw new RuntimeException(sprintf('Cannot call method %s::%s', get_class($item), $method));
            }

            return call_user_func_array($callable, $args);
        });
    }

    /**
     * Is the collection empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->stack);
    }

    /**
     * Is the collection not empty?
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     * Get the item at the supplied index, or null if the index does not exist.
     *
     * @param integer $index
     * @return mixed
     *    The item at the index, or null if the index does not exist.
     */
    public function itemAt($index)
    {
        return array_key_exists($index, $this->stack) ?
            $this->stack[$index] : null;
    }

    /**
     * Get the items at the supplied indexes in a new collection.
     *
     * The new collection will contain null values for any indexes that do not
     * exist in the collection. The new collection will have its values in the
     * order that the indexes were requested.
     *
     * @param array $indexes
     * @return Collection
     *    the new collection containing only the values of the requested indexes.
     */
    public function itemsAt(array $indexes)
    {
        $collection = new static();

        foreach ($indexes as $index) {
            $collection->push($this->itemAt($index));
        }

        return $collection;
    }

    /**
     * Get the last item in the collection.
     *
     * @return mixed
     *    The last item in the collection or null if the collection is empty.
     */
    public function last()
    {
        return !empty($this->stack) ? $this->stack[count($this) - 1] : null;
    }

    /**
     * Maps all of the items in the collection to a new value, returning a new collection.
     *
     * The callback provided should return the new value, and should have the
     * following signature:
     *
     * `function($item, $index)`
     *
     * Where `$item` is the current item in the iteration and `$index` is the
     * current index in the iteration.
     *
     * @param callable $callback
     *     The supplied callback.
     * @return Collection
     *     The new collection containing the mapped values.
     */
    public function map(callable $callback)
    {
        $collection = new static();

        foreach ($this as $key => $value) {
            $collection->push(call_user_func($callback, $value, $key));
        }

        return $collection;
    }

    /**
     * Pad the collection with the supplied value.
     *
     * @param $size
     * @param mixed|null $value
     * @return Collection
     */
    public function pad($size, $value = null)
    {
        $collection = new static();
        $collection->stack = array_pad($this->stack, $size, $value);

        return $collection;
    }

    /**
     * Removes the last item from the collection and returns it.
     *
     * @return mixed
     *    The last item or null if the collection is empty.
     */
    public function pop()
    {
        return array_pop($this->stack);
    }

    /**
     * Adds the supplied item to the end of the collection.
     *
     * @param array ...$items
     * @return $this
     */
    public function push(...$items)
    {
        foreach ($items as $item) {
            $this->stack[] = $item;
        }

        return $this;
    }

    /**
     * Adds the supplied items to the end of the collection.
     *
     * @param array|Traversable
     *     the items to be added.
     * @return $this
     * @deprecated use `push` instead
     */
    public function pushMany($items)
    {
        $this->push(...$items);

        return $this;
    }

    /**
     * Add an item to the collection if it is an object.
     *
     * @param mixed $object
     * @return $this
     * @deprecated use `pushObjects` instead
     */
    public function pushObject($object)
    {
        $this->pushObjects($object);

        return $this;
    }

    /**
     * Add any items that are objects to the collection.
     *
     * @param array ...$objects
     * @return $this
     */
    public function pushObjects(...$objects)
    {
        foreach ($objects as $object) {
            if (is_object($object)) {
                $this->stack[] = $object;
            }
        }

        return $this;
    }

    /**
     * Combines the values of the collection into a single value.
     *
     * This method expects the callback to have the following signature:
     *
     * `function($previousValue, $item, $index)`
     *
     * `$previousValue` is the value returned by the previous iteration;
     * `$item` is the current item in the iteration; and
     * `$index` is the current index in the iteration.
     *
     * If `$initialValue` is not provided, it will be `null` on the first
     * iteration.
     *
     * @param callable $callback
     *    The supplied callback.
     * @param mixed $initialValue
     *    The value to be provided to the first iteration.
     * @return mixed
     *    The reduced value.
     */
    public function reduce(callable $callback, $initialValue = null)
    {
        foreach ($this as $key => $value) {
            $initialValue = call_user_func($callback, $initialValue, $value, $key);
        }

        return $initialValue;
    }

    /**
     * Returns a new collection containing values for which the callback returns false.
     *
     * This method is the opposite of `filter`. The callback provided should
     * have the following signature:
     *
     * `function($item, $index)`
     *
     * Where `$item` is the item in the current iteration and `$index` is the
     * index of the current iteration.
     *
     * @param callable $callback
     *   The supplied callback
     * @return Collection
     *   The new collection containing items for which the callback returned false.
     */
    public function reject(callable $callback)
    {
        $collection = new static();

        foreach ($this as $key => $value) {

            if (false == call_user_func($callback, $value, $key)) {
                $collection->push($value);
            }
        }

        return $collection;
    }

    /**
     * Removes all instances of the supplied items from this collection.
     *
     * @param array ...$items
     *    The items to remove.
     * @return $this
     */
    public function remove(...$items)
    {
        $this->stack = $this->reject(function ($item) use ($items) {
            return in_array($item, $items);
        })->all();

        return $this;
    }

    /**
     * Removes the item at the specified index.
     *
     * Removes the item in the collection at the specified index. Optionally
     * multiple items starting at that index can be removed if a length is passed
     * into the method.
     *
     * @param integer $index
     *    The index to remove.
     * @param integer $length
     *    The number of indexes to remove.
     * @return $this
     */
    public function removeAt($index, $length = 1)
    {
        if (!is_int($index) || $index < 0) {
            throw new InvalidArgumentException('Expecting index to be a positive integer.');
        } elseif (!is_int($length) || $length < 1) {
            throw new InvalidArgumentException('Expecting length to be an integer that is one or greater.');
        } elseif ($this->count() <= $index) {
            throw new OutOfBoundsException("Index $index is out of bounds.");
        }

        array_splice($this->stack, $index, $length);

        return $this;
    }

    /**
     * Removes all instances of the supplied items from this collection using strict comparison.
     *
     * @param array ...$items
     * @return $this
     */
    public function removeStrict(...$items)
    {
        $this->stack = $this->reject(function ($item) use ($items) {
            return in_array($item, $items, true);
        })->all();

        return $this;
    }

    /**
     * Removes all instances of the supplied items from the collection.
     *
     * @param array|Traversable $items
     *    The items to remove.
     * @param bool $strict
     * @return $this
     * @deprecated use `remove` or `removeStrict`
     */
    public function removeMany($items, $strict = false)
    {
        if ($strict) {
            $this->removeStrict(...$items);
        } else {
            $this->remove(...$items);
        }

        return $this;
    }

    /**
     * Replace the collection with the supplied items.
     *
     * @param array ...$items
     * @return $this
     */
    public function replace(...$items)
    {
        $this->stack = $items;

        return $this;
    }

    /**
     * Copy the collection, cloning any objects within the collection.
     *
     * @return Collection
     */
    public function replicate()
    {
        return $this->map(function ($item) {
            return is_object($item) ? clone $item : $item;
        });
    }

    /**
     * Returns a copy of the collection in reverse order.
     *
     * @return Collection
     */
    public function reverse()
    {
        $collection = new static();
        $collection->stack = array_reverse($this->stack);

        return $collection;
    }

    /**
     * Searches for the supplied item and returns the corresponding key if successful.
     *
     * @param mixed $item
     * @return integer|false
     *    The integer key if found, or false if not found.
     */
    public function search($item)
    {
        return array_search($item, $this->stack);
    }

    /**
     * Searches for the supplied item using strict comparison and returns the corresponding key if successful.
     *
     * @param $item
     * @return integer|false
     *    The integer key if found, or false if not found.
     */
    public function searchStrict($item)
    {
        return array_search($item, $this->stack, true);
    }

    /**
     * Removes and returns the first value from the collection.
     *
     * @return mixed
     *    The first value or null if the collection is empty.
     */
    public function shift()
    {
        return array_shift($this->stack);
    }

    /**
     * Returns a new collection that is a slice of this collection.
     *
     * The returned collection will not maintain the array keys in this
     * collection, so will be indexed from zero. If `$begin` is negative, the slice
     * will count backwards from the end of the collection. `$end` defines how
     * many indexes should be included (i.e. the length).
     *
     * @param integer $begin
     *    The index to start the slice from.
     * @param integer|null $end
     *    The number of indexes to include, or null if no limit.
     * @return Collection
     *    The new collection.
     */
    public function slice($begin, $end = null)
    {
        $collection = new static();
        $collection->stack = array_slice($this->stack, $begin, $end, false);

        return $collection;
    }

    /**
     * Returns a new collection sorted using the provided callback.
     *
     * This method returns a new collection, sorting the content according to the
     * provided callback. The callback should have the following signature:
     *
     * `function($a, $b)`
     *
     * Where `$a` is the first item to be compared and `$b` is the second item
     * to be compared.
     *
     * To sort `$a` before `$b`, return a negative number; if `$a` is to go after
     * `$b`, a positive number should be returned. Return zero if the items are
     * equal.
     *
     * @param callable $callback The supplied callback
     * @return Collection
     *    The new collection, sorted according to the callback.
     */
    public function sort(callable $callback)
    {
        $collection = clone $this;
        usort($collection->stack, $callback);

        return $collection;
    }

    /**
     * @param $size
     * @param callable|null $callback
     * @param callable|null $overflow
     * @return Collection
     */
    public function sync($size, callable $callback = null, callable $overflow = null)
    {
        $collection = $this
            ->slice(0, $size)
            ->pad($size);

        if ($callback) {
            $collection = $collection->map($callback);
        }

        if ($overflow) {
            $this->slice($size)->map($overflow);
        }

        return $collection;
    }

    /**
     * Returns a new collection containing only unique values within this collection.
     *
     * @param $flags
     * @return Collection
     *    The new collection containing only unique values.
     */
    public function unique($flags = SORT_STRING)
    {
        $collection = new static();
        $collection->stack = array_values(array_unique($this->stack, $flags));

        return $collection;
    }

    /**
     * Returns a new collection containing only unique values, using strict comparison.
     *
     * @return Collection
     */
    public function uniqueStrict()
    {
        $collection = new static();

        $this->each(function ($item) use ($collection) {
            $collection->addStrict($item);
        });

        return $collection;
    }

    /**
     * Adds the supplied item to the start of the collection.
     *
     * @param array ...$items
     *    The items to be added.
     * @return $this
     */
    public function unshift(...$items)
    {
        $this->stack = array_merge($items, $this->stack);

        return $this;
    }

    /**
     * Adds the supplied items to the start of the collection.
     *
     * @param array|Traversable $items
     *    The items to be added to the start.
     * @return $this
     * @deprecated use `unshift`
     */
    public function unshiftMany($items)
    {
        $this->unshift(...$items);

        return $this;
    }

    /**
     * Add the supplied item to the start of the collection if it is an object.
     *
     * @param mixed $object
     * @return $this
     * @deprecated use `unshiftObjects`
     */
    public function unshiftObject($object)
    {
        $this->unshiftObjects($object);

        return $this;
    }

    /**
     * Add items that are objects to the start of the collection.
     *
     * @param array ...$objects
     * @return $this
     */
    public function unshiftObjects(...$objects)
    {
        $objects = self::create(...$objects)->filter(function ($object) {
            return is_object($object);
        })->all();

        $this->stack = array_merge($objects, $this->stack);

        return $this;
    }

    /**
     * Returns a new collection containing all values except those provided.
     *
     * @param array ...$items
     *    The items that should not be included.
     * @return Collection
     *    The new collection of values that do not match the provided value.
     */
    public function without(...$items)
    {
        return $this->copy()->remove(...$items);
    }

    /**
     * Returns a new collection containing all values except those provided, using strict comparison.
     *
     * @param array ...$items
     * @return Collection
     */
    public function withoutStrict(...$items)
    {
        return $this->copy()->removeStrict(...$items);
    }
}
