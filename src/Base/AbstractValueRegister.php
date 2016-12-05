<?php

namespace Tmd\LaravelRegisters\Base;

use Exception;
use Illuminate\Support\Collection;

/**
 * A register for actions that have data attached. e.g. Voting on a post.
 * If the object is already on the register, the value is updated
 * (unlike the AbstractBooleanRegister which throws an Exception)
 */
abstract class AbstractValueRegister extends AbstractRegister
{
    /**
     * Add the given object to the register.
     *
     * @param mixed $object
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function add($object, array $data = [])
    {
        // Delete an old value first
        if ($this->check($object)) {
            $this->destroy($object);
        }

        $response = $this->create($object, $data);

        $this->refresh();

        return $response;
    }

    /**
     * Check if the given object is on the register.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    public function check($object)
    {
        $objectKey = $this->getObjectKey($object);
        $value = isset($this->all()[$objectKey]) ? $this->objects[$objectKey] : null;

        return $value;
    }

    /**
     * Takes a Collection of database results and returns an array where the given column is the keys,
     * and the given value is the values.
     *
     * @param Collection|array $collection
     * @param string           $keyColumn
     * @param string           $valueColumn
     *
     * @return array
     */
    protected function buildObjectArrayFromCollection($collection, $keyColumn, $valueColumn)
    {
        $values = [];
        foreach ($collection as $item) {
            $key = $this->buildStringFromItemValues($item, $keyColumn);
            $values[$key] = $item->{$valueColumn};
        }

        return $values;
    }
}