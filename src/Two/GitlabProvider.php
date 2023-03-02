<?php

namespace OnixSystemsPHP\HyperfSocialite\Two;

class GitlabProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected array $scopes = ['read_user'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected string $scopeSeparator = ' ';

    /**
     * The Gitlab instance host.
     *
     * @var string
     */
    protected string $host = 'https://gitlab.com';

    /**
     * Set the Gitlab instance host.
     *
     * @param  string|null  $host
     * @return $this
     */
    public function setHost(?string $host): self
    {
        if (! empty($host)) {
            $this->host = rtrim($host, '/');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl(?string $state): string
    {
        return $this->buildAuthUrlFromBase($this->host.'/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->host.'/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        $userUrl = $this->host.'/api/v3/user?access_token='.$token;

        $response = $this->getHttpClient()->get($userUrl);

        $user = json_decode($response->getBody(), true);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => (string) $user['id'],
            'nickname' => $user['username'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar' => $user['avatar_url'],
        ]);
    }
}
