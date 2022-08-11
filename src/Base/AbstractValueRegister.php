<?php

namespace Antriver\LaravelRegisters\Base;

abstract class AbstractValueRegister extends AbstractRegister
{
    /**
     * Check if the given primary key is on the register.
     * Returns data about that entry.
     *
     * @param mixed $objectKey
     *
     * @return mixed|bool
     */
    public function checkKey($objectKey)
    {
        $value = isset($this->all()[$objectKey]) ? $this->objects[$objectKey] : false;

        return $value;
    }
}
