<?php

namespace Tmd\LaravelRegisters\Base;

abstract class AbstractBooleanRegister extends AbstractRegister
{
    /**
     * Check if the given primary key is on the register.
     * If not found: Will return boolean false.
     * If found: Will return boolean true.
     *
     * @param mixed $objectKey
     *
     * @return boolean
     */
    public function checkKey($objectKey)
    {
        return isset($this->all()[$objectKey]);
    }
}
