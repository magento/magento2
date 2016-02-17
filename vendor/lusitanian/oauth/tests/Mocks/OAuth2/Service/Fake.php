<?php

namespace OAuthTest\Mocks\OAuth2\Service;

use OAuth\OAuth2\Service\AbstractService;

class Fake extends AbstractService
{
    const SCOPE_FOO    = 'https://www.pieterhordijk.com/auth';
    const SCOPE_CUSTOM = 'custom';

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
    }
}
