<?php

namespace Xtwoend\HySocialite\Facades;

use Xtwoend\HySocialite\Contracts\Factory;

/**
 * @method static \Xtwoend\HySocialite\Contracts\Provider driver(string $driver = null)
 * @see \Xtwoend\HySocialite\SocialiteManager
 */
class Socialite
{
    protected $manager;

    public function __construct()
    {
        $this->manager = make(Factory::class);
    }

    public function __call($name, $arguments)
    {
        return call([$this->manager, $name], $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static())->{$name}(...$arguments);
    }
}
