<?php

namespace OnixSystemsPHP\HyperfSocialite\Tests\Fixtures;

class OAuthTwoWithPKCETestProviderStub extends OAuthTwoTestProviderStub
{
    protected bool $usesPKCE = true;
}
