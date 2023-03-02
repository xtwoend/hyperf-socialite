<?php

namespace OnixSystemsPHP\HyperfSocialite\Two;

use GuzzleHttp\Client;
use Hyperf\Contract\SessionInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use OnixSystemsPHP\HyperfSocialite\Contracts\Provider as ProviderContract;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

abstract class AbstractProvider implements ProviderContract
{
    /**
     * Handle session redirect
     *
     * @var \Hyperf\Contract\SessionInterface
     */
    protected SessionInterface $session;

    /**
     * The HTTP request instance.
     *
     * @var \Hyperf\HttpServer\Request
     */
    protected RequestInterface $request;

    /**
     * The HTTP Client instance.
     *
     * @var \GuzzleHttp\Client|null
     */
    protected ?Client $httpClient = null;

    /**
     * The client ID.
     *
     * @var string
     */
    protected string $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected string $clientSecret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected string $redirectUrl;

    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected array $parameters = [];

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected array $scopes = [];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected string $scopeSeparator = ',';

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
     */
    protected int $encodingType = PHP_QUERY_RFC1738;

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected bool $stateless = false;

    /**
     * Indicates if PKCE should be used.
     *
     * @var bool
     */
    protected bool $usesPKCE = false;

    /**
     * The custom Guzzle configuration options.
     *
     * @var array
     */
    protected array $guzzle = [];

    /**
     * The cached user instance.
     *
     * @var \OnixSystemsPHP\HyperfSocialite\Two\User|null
     */
    protected ?User $user = null;

    /**
     * Create a new provider instance.
     *
     * @param  \Hyperf\HttpServer\Contract\RequestInterface  $request
     * @param  string  $clientId
     * @param  string  $clientSecret
     * @param  string  $redirectUrl
     * @param  array  $guzzle
     * @return void
     */
    public function __construct(RequestInterface $request, string $clientId, string $clientSecret, string $redirectUrl, array $guzzle = [])
    {
        $this->guzzle = $guzzle;
        $this->request = $request;
        $this->clientId = $clientId;
        $this->redirectUrl = $redirectUrl;
        $this->clientSecret = $clientSecret;
        $this->session = ApplicationContext::getContainer()->get(SessionInterface::class);
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string|null $state
     * @return string
     */
    abstract protected function getAuthUrl(?string $state): string;

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    abstract protected function getTokenUrl(): string;

    /**
     * Get the raw user for the given access token.
     *
     * @param  string $token
     * @return array
     */
    abstract protected function getUserByToken(string $token): array;

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return \OnixSystemsPHP\HyperfSocialite\Two\User
     */
    abstract protected function mapUserToObject(array $user): User;

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function redirect(): PsrResponseInterface
    {
        $state = null;

        if ($this->usesState()) {
            $this->session->put('state', $state = $this->getState());
            $this->session->put($state, $this->parameters);
        }

        if ($this->usesPKCE()) {
            $this->session->put('code_verifier', $codeVerifier = $this->getCodeVerifier());
        }

        /** @var ResponseInterface $response */
        $response = make(ResponseInterface::class);

        return $response->redirect($this->getAuthUrl($state));
    }

    /**
     * Build the authentication URL for the provider from the given base URL.
     *
     * @param  string $url
     * @param  string|null $state
     * @return string
     */
    protected function buildAuthUrlFromBase(string $url, ?string $state): string
    {
        return $url.'?'.http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null $state
     * @return array
     */
    protected function getCodeFields(?string $state = null): array
    {
        $fields = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => $this->formatScopes($this->getScopes(), $this->scopeSeparator),
            'response_type' => 'code'
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        if ($this->usesPKCE()) {
            $fields['code_challenge'] = $this->getCodeChallenge();
            $fields['code_challenge_method'] = $this->getCodeChallengeMethod();
        }

        return array_merge($fields, $this->parameters);
    }

    /**
     * Format the given scopes.
     *
     * @param  array $scopes
     * @param  string $scopeSeparator
     * @return string
     */
    protected function formatScopes(array $scopes, string $scopeSeparator): string
    {
        return implode($scopeSeparator, $scopes);
    }

    /**
     * {@inheritdoc}
     */
    public function user(): User
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        $this->user = $this->mapUserToObject($this->getUserByToken(
            $token = Arr::get($response, 'access_token')
        ));

        return $this->user->setToken($token)
                    ->setRefreshToken(Arr::get($response, 'refresh_token'))
                    ->setExpiresIn(Arr::get($response, 'expires_in'));
    }

    /**
     * Get a Social User instance from a known access token.
     *
     * @param  string  $token
     * @return \OnixSystemsPHP\HyperfSocialite\Two\User
     */
    public function userFromToken(string $token): User
    {
        $user = $this->mapUserToObject($this->getUserByToken($token));

        return $user->setToken($token);
    }

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function hasInvalidState(): bool
    {
        if ($this->isStateless()) {
            return false;
        }

        $state = $this->session->get('state');
        $this->session->remove('state');

        return ! (strlen($state) > 0 && $this->request->input('state') === $state);
    }

    /**
     * Get the access token response for the given code.
     *
     * @param  string $code
     * @return array
     */
    public function getAccessTokenResponse(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string $code
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        $fields = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];

        if ($this->usesPKCE()) {
            $fields['code_verifier'] = $this->session->get('code_verifier');
            $this->session->remove('code_verifier');
        }

        return $fields;
    }

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function getCode(): string
    {
        $code = $this->request->input('code');
        if (empty($code)) {
            throw new InvalidCodeException();
        }

        return $code;
    }

    /**
     * Merge the scopes of the requested access.
     *
     * @param  array|string  $scopes
     * @return $this
     */
    public function scopes(array|string $scopes): self
    {
        $this->scopes = array_unique(array_merge($this->scopes, (array) $scopes));

        return $this;
    }

    /**
     * Set the scopes of the requested access.
     *
     * @param  array|string  $scopes
     * @return $this
     */
    public function setScopes(array|string $scopes): self
    {
        $this->scopes = array_unique((array) $scopes);

        return $this;
    }

    /**
     * Get the current scopes.
     *
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Set the redirect URL.
     *
     * @param  string  $url
     * @return $this
     */
    public function redirectUrl(string $url): self
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient(): Client
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client($this->guzzle);
        }

        return $this->httpClient;
    }

    /**
     * Set the Guzzle HTTP client instance.
     *
     * @param  \GuzzleHttp\Client  $client
     * @return $this
     */
    public function setHttpClient(Client $client): self
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * Set the request instance.
     *
     * @param  \Hyperf\HttpServer\Contract\RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Determine if the provider is operating with state.
     *
     * @return bool
     */
    protected function usesState(): bool
    {
        return ! $this->stateless;
    }

    /**
     * Determine if the provider is operating as stateless.
     *
     * @return bool
     */
    protected function isStateless(): bool
    {
        return $this->stateless;
    }

    /**
     * Indicates that the provider should operate as stateless.
     *
     * @return $this
     */
    public function stateless(): self
    {
        $this->stateless = true;

        return $this;
    }

    /**
     * Get the string used for session state.
     *
     * @return string
     */
    protected function getState(): string
    {
        return Str::random(40);
    }

    /**
     * Determine if the provider uses PKCE.
     *
     * @return bool
     */
    protected function usesPKCE(): bool
    {
        return $this->usesPKCE;
    }

    /**
     * Enables PKCE for the provider.
     *
     * @return $this
     */
    protected function enablePKCE(): self
    {
        $this->usesPKCE = true;

        return $this;
    }

    /**
     * Generates a random string of the right length for the PKCE code verifier.
     *
     * @return string
     */
    protected function getCodeVerifier(): string
    {
        return Str::random(96);
    }

    /**
     * Generates the PKCE code challenge based on the PKCE code verifier in the session.
     *
     * @return string
     */
    protected function getCodeChallenge(): string
    {
        $hashed = hash('sha256', $this->session->get('code_verifier'), true);

        return rtrim(strtr(base64_encode($hashed), '+/', '-_'), '=');
    }

    /**
     * Returns the hash method used to calculate the PKCE code challenge.
     *
     * @return string
     */
    protected function getCodeChallengeMethod(): string
    {
        return 'S256';
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param  array $parameters
     * @return $this
     */
    public function with(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }
}
