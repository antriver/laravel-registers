<?php

namespace Antriver\LaravelRegisters\Tests;

use Illuminate\Database\Eloquent\Model;
use Antriver\LaravelRegisters\Interfaces\RegisterInterface;
use Antriver\LaravelRegisters\Tests\Models\Post;
use Antriver\LaravelRegisters\Tests\Models\User;

trait RegisterTestSetupTrait
{
    /**
     * @var Post
     */
    protected $post;

    abstract protected function createRegister(Model $owner): RegisterInterface;

    public function tearDown(): void
    {
        \DB::delete("TRUNCATE TABLE post_likes");
        \DB::delete("TRUNCATE TABLE post_votes");
        \DB::delete("TRUNCATE TABLE posts");
        \DB::delete("TRUNCATE TABLE users");
        \Cache::flush();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'laravel-registers-tests');
        $app['config']->set(
            'database.connections.laravel-registers-tests',
            [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => 'laravel-registers-tests',
                'username' => 'root',
                'password' => 'root',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'engine' => null,
            ]
        );

        $app['config']->set('cache.default', 'array');
        $app['config']->set(
            'cache.stores',
            [
                'array' => [
                    'driver' => 'array',
                ],
            ]
        );
    }

    protected function createPost()
    {
        return new Post(['id' => 50]);
    }

    protected function createUser()
    {
        return new User(['id' => 99]);
    }
}
