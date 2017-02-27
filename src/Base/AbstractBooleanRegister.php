<?php

namespace Tmd\LaravelRegisters\Base;

use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class AbstractBooleanRegister extends AbstractRegister
{
    /**
     * Check if the given object is on the register.
     *
     * @param EloquentModel $object
     *
     * @return bool
     */
    public function check(EloquentModel $object)
    {
        $objectKey = $object->getKey();

        return isset($this->all()[$objectKey]);
    }
}
