<?php

namespace OnixSystemsPHP\HyperfSocialite\Contracts;

interface Factory
{
    /**
     * Get an OAuth provider implementation.
     *
     * @param string|null $driver
     * @return \OnixSystemsPHP\HyperfSocialite\Contracts\Provider
     */
    public function driver(string|null $driver = null): Provider;

    /**
     * Make an OAuth provider implementation.
     *
     * @param string $provider
     * @param array $config
     * @return \OnixSystemsPHP\HyperfSocialite\Contracts\Provider
     */
    public function buildProvider(string $provider, array $config): Provider;
}
