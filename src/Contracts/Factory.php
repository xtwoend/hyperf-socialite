<?php

namespace Xtwoend\HySocialite\Contracts;

interface Factory
{
    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     * @return \Xtwoend\HySocialite\Contracts\Provider
     */
    public function driver($driver = null);
}
