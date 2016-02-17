<?php
/**
 * Vimeo service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://developer.vimeo.com/
 * @link    https://developer.vimeo.com/api/authentication
 */

namespace OAuth\OAuth2\Service;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;

/**
 * Vimeo service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://developer.vimeo.com/
 * @link    https://developer.vimeo.com/api/authentication
 */
class Vimeo extends AbstractService
{
    // API version
    const VERSION = '3.2';
    // API Header Accept
    const HEADER_ACCEPT = 'application/vnd.vimeo.*+json;version=3.2';

    /**
     * Scopes
     * @see  https://developer.vimeo.com/api/authentication#scope
     */
    // View public videos
    const SCOPE_PUBLIC    = 'public';
    // View private videos
    const SCOPE_PRIVATE   = 'private';
    // View Vimeo On Demand purchase history
    const SCOPE_PURCHASED = 'purchased';
    // Create new videos, groups, albums, etc.
    const SCOPE_CREATE    = 'create';
    // Edit videos, groups, albums, etc.
    const SCOPE_EDIT      = 'edit';
    // Delete videos, groups, albums, etc.
    const SCOPE_DELETE    = 'delete';
    // Interact with a video on behalf of a user, such as liking
    // a video or adding it to your watch later queue
    const SCOPE_INTERACT  = 'interact';
    // Upload a video
    const SCOPE_UPLOAD    = 'upload';

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct(
            $credentials,
            $httpClient,
            $storage,
            $scopes,
            $baseApiUri,
            true
        );

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://api.vimeo.com/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://api.vimeo.com/oauth/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://api.vimeo.com/oauth/access_token');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_HEADER_BEARER;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error_description'])) {
            throw new TokenResponseException(
                'Error in retrieving token: "' . $data['error_description'] . '"'
            );
        } elseif (isset($data['error'])) {
            throw new TokenResponseException(
                'Error in retrieving token: "' . $data['error'] . '"'
            );
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);

        if (isset($data['expires_in'])) {
            $token->setLifeTime($data['expires_in']);
            unset($data['expires_in']);
        }
        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);

        $token->setExtraParams($data);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraOAuthHeaders()
    {
        return array('Accept' => self::HEADER_ACCEPT);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraApiHeaders()
    {
        return array('Accept' => self::HEADER_ACCEPT);
    }
}
