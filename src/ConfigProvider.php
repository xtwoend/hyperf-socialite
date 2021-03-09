<?php

namespace Xtwoend\HySocialite;

use Xtwoend\HySocialite\SocialiteManager;
use Xtwoend\HySocialite\Contracts\Factory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Factory::class => SocialiteManager::class
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for socialite.',
                    'source' => __DIR__ . '/../publish/socialite.php',
                    'destination' => BASE_PATH . '/config/socialite.php',
                ],
            ],
        ];
    }
}
