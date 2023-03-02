<?php

namespace OnixSystemsPHP\HyperfSocialite\Two;

use OnixSystemsPHP\HyperfSocialite\AbstractUser;

class User extends AbstractUser
{
    /**
     * The user's access token.
     */
    public string $token;

    /**
     * The refresh token that can be exchanged for a new access token.
     */
    public ?string $refreshToken;

    /**
     * The number of seconds the access token is valid for.
     */
    public ?int $expiresIn;

    /**
     * Set the token on the user.
     *
     * @param  string $token
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set the refresh token required to obtain a new access token.
     *
     * @param  string|null  $refreshToken
     * @return $this
     */
    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Set the number of seconds the access token is valid for.
     *
     * @param  int|null $expiresIn
     * @return $this
     */
    public function setExpiresIn(?int $expiresIn): self
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }
}
