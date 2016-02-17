<?php

namespace OAuthTest\Mocks\OAuth1\Service;

use OAuth\OAuth1\Service\AbstractService;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\OAuth1\Signature\SignatureInterface;
use OAuth\Common\Http\Uri\UriInterface;

class Fake extends AbstractService
{
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        SignatureInterface $signature,
        UriInterface $baseApiUri = null
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenEndpoint()
    {
    }

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
    protected function parseRequestTokenResponse($responseBody)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
    }
}
