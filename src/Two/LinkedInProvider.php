<?php

namespace OnixSystemsPHP\HyperfSocialite\Two;

use Hyperf\Utils\Arr;

class LinkedInProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected array $scopes = ['r_liteprofile', 'r_emailaddress'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected string $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl(?string $state): string
    {
        return $this->buildAuthUrlFromBase('https://www.linkedin.com/oauth/v2/authorization', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        $basicProfile = $this->getBasicProfile($token);
        $emailAddress = $this->getEmailAddress($token);

        return array_merge($basicProfile, $emailAddress);
    }

    /**
     * Get the basic profile fields for the user.
     *
     * @param  string  $token
     * @return array
     */
    protected function getBasicProfile(string $token): array
    {
        $url = 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))';

        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * Get the email address for the user.
     *
     * @param  string  $token
     * @return array
     */
    protected function getEmailAddress(string $token): array
    {
        $url = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';

        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ]);

        return (array) Arr::get((array) json_decode($response->getBody(), true), 'elements.0.handle~');
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        $preferredLocale = Arr::get($user, 'firstName.preferredLocale.language').'_'.Arr::get($user, 'firstName.preferredLocale.country');
        $firstName = Arr::get($user, 'firstName.localized.'.$preferredLocale);
        $lastName = Arr::get($user, 'lastName.localized.'.$preferredLocale);

        $images = (array) Arr::get($user, 'profilePicture.displayImage~.elements', []);
        $avatar = Arr::first($images, function ($image) {
            return $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] === 100;
        });
        $originalAvatar = Arr::first($images, function ($image) {
            return $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] === 800;
        });

        return (new User)->setRaw($user)->map([
            'id' => (string) $user['id'],
            'nickname' => null,
            'name' => $firstName.' '.$lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => Arr::get($user, 'emailAddress'),
            'avatar' => Arr::get($avatar, 'identifiers.0.identifier'),
            'avatar_original' => Arr::get($originalAvatar, 'identifiers.0.identifier'),
        ]);
    }
}
