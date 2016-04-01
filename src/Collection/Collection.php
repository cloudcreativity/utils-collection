<?php

namespace CloudCreativity\Utils\Collection;

use Countable;
use InvalidArgumentException;
use Iterator;
use OutOfBoundsException;
use stdClass;
use Traversable;

class Collection implements Iterator, Countable
{

    /**
     * @var integer
     */
    private $position = 0;

    /**
     * @var array
     */
    private $stack = [];

    /**
     * Collection constructor.
     * @param array $items
     */
    public function __construct($items = array())
    {
        if ($items instanceof static) {
            $items = $items->toArray();
        } elseif (is_array($items)) {
            $items = array_values($items);
        } else {
            throw new InvalidArgumentException('Expecting an array or a Collection object.');
        }

        $this->stack = $items;
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->stack[$this->position];
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return array_key_exists($this->position, $this->stack);
    }

    /**
     * Adds the item if it is not already in the collection.
     *
     * @param mixed $item
     * @param bool $strict
     * @return $this
     */
    public function add($item, $strict = false)
    {
        if (!in_array($item, $this->stack, $strict)) {
            $this->push($item);
        }

        return $this;
    }

    /**
     * Adds any items that are not already in the collection.
     *
     * @param array|Traversable $items
     * @param bool $strict
     * @return $this
     */
    public function addMany($items, $strict = false)
    {
        foreach ($this->cast($items) as $item) {
            $this->add($item, $strict);
        }

        return $this;
    }

    /**
     * Add an item if it is an object and is not already in the collection.
     *
     * @param mixed $object
     * @param bool $strict
     * @return $this
     */
    public function addObject($object, $strict = false)
    {
        if (is_object($object)) {
            $this->add($object, $strict);
        }

        return $this;
    }

    /**
     * Adds any items that are an object and not already in the collection.
     *
     * @param array|Traversable $objects
     * @param bool $strict
     * @return $this
     */
    public function addObjects($objects, $strict = false)
    {
        foreach ($this->cast($objects) as $object) {
            $this->addObject($object);
        }

        return $this;
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
            $ret[] = new static($chunk);
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
     * Returns true if the supplied item is found within the collection.
     *
     * @param mixed $item
     * @param bool $strict
     * @return boolean
     */
    public function contains($item, $strict = false)
    {
        return false !== $this->search($item, $strict);
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
     * Returns true if the supplied collection is equal to the current collection.
     *
     * This method returns true if the collection within `$compare` is the same
     * as the collection within this collection. The class of `$this` and
     * `$object` is not factored in.
     *
     * @param array|Traversable $compare
     * @param bool $strict
     * @return bool
     */
    public function equals($compare, $strict = false)
    {
        $compare = $this->cast($compare);

        return (false == $strict) ?
            $this->stack == $compare->stack :
            $this->stack === $compare->stack;
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
     * @param bool $strict
     * @return integer|false
     *     The integer index in the array, or false if no matches.
     * @throws OutOfBoundsException
     *     If `$startAt` is out of bounds.
     */
    public function indexOf($item, $startAt = 0, $strict = false)
    {
        if (count($this) <= $startAt) {
            throw new OutOfBoundsException(sprintf('Index "%s" is out of bounds.', $startAt));
        }

        for ($i = $startAt; $i < count($this->stack); $i++) {

            $value = $this->stack[$i];

            if ((!$strict && $item == $value) || ($strict && $item === $value)) {
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

        array_splice($this->stack, $index, 0, array($item));

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
     * @param string $method
     * @param mixed $arg
     *      argument or array of arguments to use with the invoke method.
     * @return Collection
     */
    public function invoke($method, $args)
    {
        $args = (array) $args;

        return $this->map(function ($item) use ($methods, $args) {
            if (!is_object($item)) {
                return null;
            }

            $callable = [$item, $method];

            if (!is_callable($callable)) {
                throw new InvalidArgumentException(sprintf('Cannot call %s on object %s', $method, get_class($object)));
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
     * @return static
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
     * @param mixed $item
     * @return $this
     */
    public function push($item)
    {
        $this->stack[] = $item;

        return $this;
    }

    /**
     * Adds the supplied items to the end of the collection.
     *
     * @param array|Traversable
     *     the items to be added.
     * @return $this
     */
    public function pushMany($items)
    {
        foreach ($this->cast($items) as $value) {
            $this->push($value);
        }

        return $this;
    }

    /**
     * Add an item to the collection if it is an object.
     *
     * @param mixed $object
     * @return $this
     */
    public function pushObject($object)
    {
        if (is_object($object)) {
            $this->push($object);
        }

        return $this;
    }

    /**
     * Add any items that are objects to the collection.
     *
     * @param array|Traversable $object
     * @return $this
     */
    public function pushObjects($objects)
    {
        foreach ($this->cast($objects) as $object) {
            if (is_object($object)) {
                $this->pushObject($object);
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
     * Removes all instances of the supplied item from this collection.
     *
     * @param mixed $item
     *    The item to remove.
     * @param bool $strict
     * @return $this
     */
    public function remove($item, $strict = false)
    {
        $stack = [];

        foreach ($this as $value) {
            if (($strict && $item !== $value) || (!$strict && $item != $value)) {
                $stack[] = $value;
            }
        }

        $this->stack = $stack;

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
     * Removes all instances of the supplied items from the collection.
     *
     * @param array|Traversable $items
     *    The items to remove.
     * @param bool $strict
     * @return $this
     */
    public function removeMany($items, $strict = false)
    {
        $stack = array();

        if (!is_array($items)) {
            $items = $this->cast($items)->toArray();
        }

        foreach ($this as $value) {

            if (!in_array($value, $items, $strict)) {
                $stack[] = $value;
            }
        }

        $this->stack = $stack;

        return $this;
    }

    /**
     * Replace the collection with the supplied items.
     *
     * @param array|Traversable $items
     * @return $this
     */
    public function replace($items)
    {
        $this->stack = $this->cast($items)->toArray();

        return $this;
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
     * @param bool $strict
     * @return integer|false
     *    The integer key if found, or false if not found.
     */
    public function search($item, $strict = false)
    {
        return array_search($item, $this->stack, $strict);
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
        $collection = new static();

        $collection->stack = $this->stack;

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
     * Return an array copy of this collection.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->stack;
    }

    /**
     * Returns a new collection containing only unique values within this collection.
     *
     * @param bool $strict
     * @return Collection
     *    The new collection containing only unique values.
     */
    public function unique($strict = false)
    {
        $collection = new static();

        foreach ($this as $value) {
            $collection->add($value, $strict);
        }

        return $collection;
    }

    /**
     * Adds the supplied item to the start of the collection.
     *
     * @param mixed $item
     *    The item to be added.
     * @return $this
     */
    public function unshift($item)
    {
        array_unshift($this->stack, $item);

        return $this;
    }

    /**
     * Adds the supplied items to the start of the collection.
     *
     * @param array|Traversable $items
     *    The items to be added to the start.
     * @return $this
     */
    public function unshiftMany($items)
    {
        $this->stack = array_merge($this->cast($items)->toArray(), $this->stack);

        return $this;
    }

    /**
     * Add the supplied item to the start of the collection if it is an object.
     *
     * @param mixed $object
     * @return $this
     */
    public function unshiftObject($object)
    {
        if (is_object($object)) {
            $this->unshift($object);
        }

        return $this;
    }

    /**
     * Add items that are objects to the start of the collection.
     *
     * @param array|Traversable
     * @return $this
     */
    public function unshiftObjects($objects)
    {
        foreach ($this->cast($objects) as $object) {
            $this->unshift($object);
        }

        return $this;
    }

    /**
     * Returns a new collection containing all values except the one provided.
     *
     * @param mixed $item
     *    The item that should not be included.
     * @param bool $strict
     * @return Collection
     *    The new collection of values that do not match the provided value.
     */
    public function without($item, $strict = false)
    {
        return $this->filter(function ($value) use ($item, $strict) {
            return ($strict && $item !== $value) || (!$strict && $item != $value);
        });
    }

    /**
     * Cast the supplied collection to a Collection object.
     *
     * If `$collection` is already a Collection, then the same object
     * will be returned. If it is an array or Traversable object, it will be
     * converted to a Collection object.
     *
     * @param array|Traversable
     * @return Collection
     */
    public static function cast($items)
    {
        if ($items instanceof static) {
            return $items;
        } elseif (is_array($items) || $items instanceof self) {
            return new static($items);
        } elseif (!$items instanceof Traversable && !$items instanceof stdClass) {
            throw new InvalidArgumentException('Expecting a Traversable object or an array.');
        }

        $cast = new static();

        foreach ($items as $value) {
            $cast->push($value);
        }

        return $cast;
    }
}
