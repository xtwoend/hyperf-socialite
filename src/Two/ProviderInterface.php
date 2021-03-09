<?php

namespace Xtwoend\HySocialite\Two;

interface ProviderInterface
{
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Hyperf\HttpMessage\Server\Response
     */
    public function redirect();

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Xtwoend\HySocialite\Two\User
     */
    public function user();
}
