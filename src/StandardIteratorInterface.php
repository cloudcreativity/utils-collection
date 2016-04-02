<?php

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
