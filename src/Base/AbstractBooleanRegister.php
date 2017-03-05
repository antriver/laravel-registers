<?php

namespace Tmd\LaravelRegisters\Base;

abstract class AbstractBooleanRegister extends AbstractRegister
{
    /**
     * Check if the given key is on the register.
     *
     * @param mixed $objectKey
     *
     * @return bool
     */
    public function checkKey($objectKey)
    {
        return isset($this->all()[$objectKey]);
    }
}
