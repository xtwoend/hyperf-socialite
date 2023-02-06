<?php

namespace OnixSystemsPHP\HyperfSocialite\Contracts;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface Provider
{
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function redirect(): PsrResponseInterface;

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \OnixSystemsPHP\HyperfSocialite\Contracts\User
     */
    public function user(): User;
}
