<?php

namespace Antriver\LaravelRegisters\Tests;

use Cache;
use Orchestra\Testbench\TestCase;
use Antriver\LaravelRegisters\Exceptions\AlreadyOnRegisterException;
use Antriver\LaravelRegisters\Exceptions\NotOnRegisterException;

abstract class RegisterTestCase extends TestCase
{
    use RegisterTestSetupTrait;

    public function testAddDuplicateObjectThrowsException()
    {
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $register->add($user, $this->defaultData);

        $this->expectException(AlreadyOnRegisterException::class);

        $register->add($user, $this->defaultData);
    }

    public function testRemoveObjectThrowsException()
    {
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $this->assertEmpty($register->all());
        $this->assertFalse($register->check($user));
        $this->assertSame(0, $register->count());

        $this->expectException(NotOnRegisterException::class);

        $register->remove($user);
    }

    public function testObjectsAreStoredInCache()
    {
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $register->add($user, $this->defaultData);

        $this->assertSame(
            $this->expectedStoredData,
            Cache::get($register->publicGetCacheKey())
        );
    }

    public function testCacheIsCleared()
    {
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $register->add($user, $this->defaultData);

        $this->assertSame(
            $this->expectedStoredData,
            Cache::get($register->publicGetCacheKey())
        );

        $register->remove($user);

        $this->assertSame(
            [],
            Cache::get($register->publicGetCacheKey())
        );
    }

    public function testCachedCopyIsUsed()
    {
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $this->assertNull(
            Cache::get($register->publicGetCacheKey())
        );

        $this->assertEmpty($register->all());
        $this->assertFalse($register->check($user));
        $this->assertSame(0, $register->count());

        $this->assertInternalType(
            'array',
            Cache::get($register->publicGetCacheKey())
        );

        $this->assertSame(
            [],
            Cache::get($register->publicGetCacheKey())
        );

        // Set something else in the cached array and ensure that is what is returned.
        Cache::put($register->publicGetCacheKey(), [123 => true], 1);

        // Create a new register so the $object property cache is not used.
        unset($register);
        $register2 = $this->createRegister($post);

        $this->assertSame(
            [
                123 => true,
            ],
            $register2->all()
        );

        $this->assertSame(1, $register2->count());
    }

    public function testOnAddIsCalled()
    {
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $register->onAddClosure = function ($model) use ($user) {
            $this->assertSame($model, $user);
        };

        $this->assertFalse($register->onAddCalled);
        $this->assertFalse($register->onRemoveCalled);

        $register->add($user, $this->defaultData);

        $this->assertTrue($register->onAddCalled);
        $this->assertFalse($register->onRemoveCalled);
    }

    public function testOnRemoveIsCalled()
    {
        // Create one register to add it, destroy that, and add another one to test the onRemove.
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $register->add($user, $this->defaultData);

        unset($register);

        $register = $this->createRegister($post);

        $this->assertFalse($register->onAddCalled);
        $this->assertFalse($register->onRemoveCalled);

        $register->remove($user);

        $onRemoveWasCalled = false;
        $register->onRemoveClosure = function ($model) use ($user, &$onRemoveWasCalled) {
            if ($onRemoveWasCalled) {
                throw new \Exception("onRemove should only be called once.");
            }
            $this->assertSame($model, $user);
            $onRemoveWasCalled = true;
        };

        $this->assertFalse($register->onAddCalled);
        $this->assertTrue($register->onRemoveCalled);
    }
}
