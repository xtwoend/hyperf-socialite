<?php

namespace OnixSystemsPHP\HyperfSocialite;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use InvalidArgumentException;
use League\OAuth1\Client\Server\Twitter as TwitterServer;
use OnixSystemsPHP\HyperfSocialite\One\TwitterProvider;
use OnixSystemsPHP\HyperfSocialite\Two\BitbucketProvider;
use OnixSystemsPHP\HyperfSocialite\Two\FacebookProvider;
use OnixSystemsPHP\HyperfSocialite\Two\GithubProvider;
use OnixSystemsPHP\HyperfSocialite\Two\GitlabProvider;
use OnixSystemsPHP\HyperfSocialite\Two\GoogleProvider;
use OnixSystemsPHP\HyperfSocialite\Two\LinkedInProvider;

class SocialiteManager extends Manager implements Contracts\Factory
{
    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with(string $driver): mixed
    {
        return $this->driver($driver);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \OnixSystemsPHP\HyperfSocialite\Two\AbstractProvider
     */
    protected function createGithubDriver(): Two\AbstractProvider
    {
        $config = $this->config->get('socialite.github');

        return $this->buildProvider(
            GithubProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \OnixSystemsPHP\HyperfSocialite\Two\AbstractProvider
     */
    protected function createFacebookDriver(): Two\AbstractProvider
    {
        $config = $this->config->get('socialite.facebook');

        return $this->buildProvider(
            FacebookProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \OnixSystemsPHP\HyperfSocialite\Two\AbstractProvider
     */
    protected function createGoogleDriver(): Two\AbstractProvider
    {
        $config = $this->config->get('socialite.google');

        return $this->buildProvider(
            GoogleProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \OnixSystemsPHP\HyperfSocialite\Two\AbstractProvider
     */
    protected function createLinkedinDriver(): Two\AbstractProvider
    {
        $config = $this->config->get('socialite.linkedin');

        return $this->buildProvider(
          LinkedInProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \OnixSystemsPHP\HyperfSocialite\Two\AbstractProvider
     */
    protected function createBitbucketDriver(): Two\AbstractProvider
    {
        $config = $this->config->get('socialite.bitbucket');

        return $this->buildProvider(
          BitbucketProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \OnixSystemsPHP\HyperfSocialite\Two\AbstractProvider
     */
    protected function createGitlabDriver(): Two\AbstractProvider
    {
        $config = $this->config->get('socialite.gitlab');

        return $this->buildProvider(
            GitlabProvider::class, $config
        )->setHost($config['host'] ?? null);
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param string $provider
     * @param array  $config
     * @return \OnixSystemsPHP\HyperfSocialite\Two\AbstractProvider
     */
    public function buildProvider(string $provider, array $config): Two\AbstractProvider
    {
        return new $provider(
            $this->container->make(RequestInterface::class), $config['client_id'],
            $config['client_secret'], $this->formatRedirectUrl($config),
            Arr::get($config, 'guzzle', [])
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \OnixSystemsPHP\HyperfSocialite\One\AbstractProvider
     */
    protected function createTwitterDriver(): One\AbstractProvider
    {
        $config = $this->config->get('socialite.twitter');

        return new TwitterProvider(
            $this->container->make('request'), new TwitterServer($this->formatConfig($config))
        );
    }

    /**
     * Format the server configuration.
     *
     * @param  array  $config
     * @return array
     */
    public function formatConfig(array $config): array
    {
        return array_merge([
            'identifier' => $config['client_id'],
            'secret' => $config['client_secret'],
            'callback_uri' => $this->formatRedirectUrl($config),
        ], $config);
    }

    /**
     * Format the callback URL, resolving a relative URI if needed.
     *
     * @param  array  $config
     * @return string
     */
    protected function formatRedirectUrl(array $config): string
    {
        $redirect = $config['redirect'];

        return Str::startsWith($redirect, '/')
                    ? $redirect
                    : $redirect;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getDefaultDriver(): string
    {
        throw new InvalidArgumentException('No Socialite driver was specified.');
    }
}
