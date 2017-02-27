<?php

namespace Tmd\LaravelRegisters\Base;

use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class AbstractValueRegister extends AbstractRegister
{
    /**
     * Check if the given object is on the register.
     *
     * @param EloquentModel $object
     *
     * @return mixed
     */
    public function check(EloquentModel $object)
    {
        $objectKey = $object->getKey();
        $value = isset($this->all()[$objectKey]) ? $this->objects[$objectKey] : null;

        return $value;
    }
}
