<?php

namespace Xtwoend\HySocialite\Contracts;

interface Provider
{
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect();

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Xtwoend\HySocialite\Contracts\User
     */
    public function user();
}
