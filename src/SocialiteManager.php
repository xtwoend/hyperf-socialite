<?php

namespace Xtwoend\HySocialite;

use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use InvalidArgumentException;
use Xtwoend\HySocialite\Two\GithubProvider;
use Xtwoend\HySocialite\Two\GitlabProvider;
use Xtwoend\HySocialite\Two\GoogleProvider;
use Xtwoend\HySocialite\One\TwitterProvider;
use Xtwoend\HySocialite\Two\FacebookProvider;
use Xtwoend\HySocialite\Two\LinkedInProvider;
use Xtwoend\HySocialite\Two\BitbucketProvider;
use Hyperf\HttpServer\Contract\RequestInterface;
use League\OAuth1\Client\Server\Twitter as TwitterServer;

class SocialiteManager extends Manager implements Contracts\Factory
{
    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Xtwoend\HySocialite\Two\AbstractProvider
     */
    protected function createGithubDriver()
    {
        $config = $this->config->get('socialite.github');

        return $this->buildProvider(
            GithubProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Xtwoend\HySocialite\Two\AbstractProvider
     */
    protected function createFacebookDriver()
    {
        $config = $this->config->get('socialite.facebook');

        return $this->buildProvider(
            FacebookProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Xtwoend\HySocialite\Two\AbstractProvider
     */
    protected function createGoogleDriver()
    {
        $config = $this->config->get('socialite.google');

        return $this->buildProvider(
            GoogleProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Xtwoend\HySocialite\Two\AbstractProvider
     */
    protected function createLinkedinDriver()
    {
        $config = $this->config->get('socialite.linkedin');

        return $this->buildProvider(
          LinkedInProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Xtwoend\HySocialite\Two\AbstractProvider
     */
    protected function createBitbucketDriver()
    {
        $config = $this->config->get('socialite.bitbucket');

        return $this->buildProvider(
          BitbucketProvider::class, $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Xtwoend\HySocialite\Two\AbstractProvider
     */
    protected function createGitlabDriver()
    {
        $config = $this->config->get('socialite.gitlab');

        return $this->buildProvider(
            GitlabProvider::class, $config
        )->setHost($config['host'] ?? null);
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param  string  $provider
     * @param  array  $config
     * @return \Xtwoend\HySocialite\Two\AbstractProvider
     */
    public function buildProvider($provider, $config)
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
     * @return \Xtwoend\HySocialite\One\AbstractProvider
     */
    protected function createTwitterDriver()
    {
        $config = config('socialite.twitter');

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
    public function formatConfig(array $config)
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
    protected function formatRedirectUrl(array $config)
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
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No Socialite driver was specified.');
    }
}
