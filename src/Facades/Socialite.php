<?php

namespace OnixSystemsPHP\HyperfSocialite\Facades;

use OnixSystemsPHP\HyperfSocialite\Contracts\Factory;

/**
 * @method static \OnixSystemsPHP\HyperfSocialite\Contracts\Provider driver(string $driver = null)
 * @method static \OnixSystemsPHP\HyperfSocialite\Contracts\Provider with(string $driver = null)
 * @method static \OnixSystemsPHP\HyperfSocialite\Contracts\Provider buildProvider(string $provider, array $config)
 * @see \OnixSystemsPHP\HyperfSocialite\SocialiteManager
 */
class Socialite
{
    protected Factory $manager;

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
