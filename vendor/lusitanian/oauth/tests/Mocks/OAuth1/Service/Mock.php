<?php

namespace OAuthTest\Mocks\OAuth1\Service;

use OAuth\OAuth1\Service\AbstractService;
use OAuth\Common\Http\Uri\Uri;
use OAuth\OAuth1\Token\StdOAuth1Token;

class Mock extends AbstractService
{
    public function getRequestTokenEndpoint()
    {
        return new Uri('http://pieterhordijk.com/token');
    }

    public function getAuthorizationEndpoint()
    {
        return new Uri('http://pieterhordijk.com/auth');
    }

    public function getAccessTokenEndpoint()
    {
        return new Uri('http://pieterhordijk.com/access');
    }

    protected function parseRequestTokenResponse($responseBody)
    {
        return new StdOAuth1Token();
    }

    protected function parseAccessTokenResponse($responseBody)
    {
        return new StdOAuth1Token();
    }
}
