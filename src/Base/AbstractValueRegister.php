<?php

namespace Tmd\LaravelRegisters\Base;

abstract class AbstractValueRegister extends AbstractRegister
{
    /**
     * Check if the given object is on the register.
     *
     * @param mixed $objectKey
     *
     * @return bool|mixed
     */
    public function checkKey($objectKey)
    {
        $value = isset($this->all()[$objectKey]) ? $this->objects[$objectKey] : false;

        return $value;
    }
}
