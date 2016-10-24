<?php

namespace Tmd\LaravelRegisters\Base;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Exception;

/**
 * A register for actions that are either on or off. e.g. Loving a post or following a user.
 * Throws an exception when trying to add if that object is already on the register, and vice versa.
 */
abstract class AbstractBooleanRegister extends AbstractRegister
{
    /**
     * Add the given object to the register.
     *
     * @param EloquentModel $object
     * @param array         $data
     *
     * @return mixed
     * @throws Exception
     */
    public function add(EloquentModel $object, array $data = [])
    {
        if ($this->check($object)) {
            throw $this->getAlreadyOnRegisterException($object);
        }

        $response = $this->create($object, $data);

        $this->refresh();

        return $response;
    }

    /**
     * Check if the given object is on the register.
     *
     * @param EloquentModel $object
     *
     * @return bool
     */
    public function check(EloquentModel $object)
    {
        return isset($this->all()[$object->getKey()]);
    }

    /**
     * Takes a Collection of database results and returns an array where the given column is the keys,
     * and the given value is the values.
     *
     * @param           $collection
     * @param           $keyColumn
     * @param bool|true $value
     *
     * @return array
     */
    protected function buildArrayFromCollection($collection, $keyColumn, $value = true)
    {
        $values = [];
        foreach ($collection as $item) {
            $values[$item->{$keyColumn}] = $value;
        }

        return $values;
    }
}
