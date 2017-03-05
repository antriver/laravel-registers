<?php

namespace Tmd\LaravelRegisters\Interfaces;

use Illuminate\Database\Eloquent\Model as EloquentModel;

interface RegisterInterface
{
    /**
     * Add the given object to the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param mixed $object
     * @param array $data Optional additional data to pass to the register.
     *
     * @return boolean
     */
    public function add(EloquentModel $object, array $data = []);

    /**
     * Remove the given object from the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param EloquentModel $object
     *
     * @return mixed
     */
    public function remove(EloquentModel $object);

    /**
     * Check if the given object is on the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param EloquentModel $object
     *
     * @return mixed
     */
    public function check(EloquentModel $object);

    /**
     * Check if the given object key is on the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param mixed $objectKey
     *
     * @return mixed
     */
    public function checkKey($objectKey);

    /**
     * Return all the information about all of the objects on the register.
     *
     * @return array
     */
    public function all();

    /**
     * Return a single dimensional array of the keys of the objects on the register.
     *
     * @return array
     */
    public function keys();

    /**
     * Return the number of objects on the register.
     *
     * @return int
     */
    public function count();

    /**
     * Clear any cached data about the objects on the register.
     *
     * @return mixed
     */
    public function refresh();
}
