<?php

declare(strict_types=1);
namespace OnixSystemsPHP\HyperfSocialite;

use OnixSystemsPHP\HyperfSocialite\Contracts\User;

abstract class AbstractUser implements \ArrayAccess, User
{
    /**
     * The unique identifier for the user.
     *
     * @var mixed
     */
    public string $id;

    /**
     * The user's nickname / username.
     *
     * @var string|null
     */
    public ?string $nickname;

    /**
     * The user's full name.
     *
     * @var string|null
     */
    public ?string $name;

    /**
     * The user's e-mail address.
     *
     * @var string|null
     */
    public ?string $email;

    /**
     * The user's avatar image URL.
     *
     * @var string|null
     */
    public ?string $avatar;

    /**
     * The user's raw attributes.
     *
     * @var array
     */
    public array $user;

    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the nickname / username for the user.
     *
     * @return string
     */
    public function getNickname(): string
    {
        return is_null($this->nickname) ? '' : $this->nickname;
    }

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getName(): string
    {
        return is_null($this->name) ? '' : $this->name;
    }

    /**
     * Get the e-mail address of the user.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return is_null($this->email) ? '' : $this->email;
    }

    /**
     * Get the avatar / image URL for the user.
     *
     * @return string
     */
    public function getAvatar(): string
    {
        return is_null($this->avatar) ? '' : $this->avatar;
    }

    /**
     * Get the raw user array.
     *
     * @return array
     */
    public function getRaw(): array
    {
        return $this->user;
    }

    /**
     * Set the raw user array from the provider.
     *
     * @return $this
     */
    public function setRaw(array $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Map the given array onto the user's properties.
     *
     * @return $this
     */
    public function map(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Determine if the given raw user attribute exists.
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->user);
    }

    /**
     * Get the given key from the raw user.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->user[$offset];
    }

    /**
     * Set the given attribute on the raw user array.
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->user[$offset] = $value;
    }

    /**
     * Unset the given value from the raw user array.
     *
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->user[$offset]);
    }
}
