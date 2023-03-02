<?php

namespace OnixSystemsPHP\HyperfSocialite\Tests\Fixtures;

use OnixSystemsPHP\HyperfSocialite\Two\AbstractProvider;
use OnixSystemsPHP\HyperfSocialite\Two\User;
use Mockery as m;
use stdClass;

class OAuthTwoTestProviderStub extends AbstractProvider
{
    /**
     * @var \GuzzleHttp\Client|\Mockery\MockInterface
     */
    public $http;

    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('http://auth.url', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'http://token.url';
    }

    protected function getUserByToken(string $token): array
    {
        return ['id' => 'foo'];
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User)->map(['id' => $user['id']]);
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client|\Mockery\MockInterface
     */
    protected function getHttpClient(): \GuzzleHttp\Client
    {
        if ($this->http) {
            return $this->http;
        }

        return $this->http = m::mock(stdClass::class);
    }
}
