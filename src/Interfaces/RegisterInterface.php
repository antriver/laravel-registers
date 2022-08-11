<?php

namespace Antriver\LaravelRegisters\Interfaces;

use Exception;
use Illuminate\Database\Eloquent\Model;

interface RegisterInterface
{
    /**
     * Add the given Model to the register.
     *
     * @param Model $object
     * @param array $data Optional additional data to pass to the register (needed for ValueRegister).
     *
     * @return bool
     * @throws Exception
     */
    public function add(Model $object, array $data = []): bool;

    /**
     * Remove the given Model from the register.
     *
     * @param Model $object
     *
     * @return bool
     * @throws Exception
     */
    public function remove(Model $object): bool;

    /**
     * Check if the given Model is on the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param Model $object
     *
     * @return mixed
     */
    public function check(Model $object);

    /**
     * Check if the given primary key is on the register.
     * If not found: Will return boolean false.
     * If found: Will return boolean true, or data about that entry, depending upon the implementation.
     *
     * @param mixed $objectKey
     *
     * @return mixed|bool
     */
    public function checkKey($objectKey);

    /**
     * Return all the information about all of the objects on the register.
     * Uses a cached copy if available.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Clear any cached data about the objects on the register.
     * Returns a fresh copy of information about all of the objects on the register (the same as all())
     *
     * @return array
     */
    public function refresh(): array;

    /**
     * Return a single dimensional array of the keys of the objects on the register.
     *
     * @return array
     */
    public function keys(): array;

    /**
     * Return the number of objects on the register.
     *
     * @return int
     */
    public function count(): int;
}
