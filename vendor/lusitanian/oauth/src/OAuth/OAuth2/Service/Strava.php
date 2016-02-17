<?php
/**
 * Strava service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    http://strava.github.io/
 * @link    http://strava.github.io/api/v3/oauth/
 */

namespace OAuth\OAuth2\Service;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\OAuth2\Service\Exception\InvalidAccessTypeException;

/**
 * Strava service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    http://strava.github.io/
 * @link    http://strava.github.io/api/v3/oauth/
 */
class Strava extends AbstractService
{
    /**
     * Scopes
     */
    // default
    const SCOPE_PUBLIC       = 'public';
    // Modify activities, upload on the user’s behalf
    const SCOPE_WRITE        = 'write';
    // View private activities and data within privacy zones
    const SCOPE_VIEW_PRIVATE = 'view_private';

    protected $approvalPrompt = 'auto';

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        if (empty($scopes)) {
            $scopes = array(self::SCOPE_PUBLIC);
        }

        parent::__construct(
            $credentials,
            $httpClient,
            $storage,
            $scopes,
            $baseApiUri,
            true
        );

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://www.strava.com/api/v3/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://www.strava.com/oauth/authorize?approval_prompt=' . $this->approvalPrompt);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://www.strava.com/oauth/token');
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

    public function setApprouvalPrompt($prompt)
    {
        if (!in_array($prompt, array('auto', 'force'), true)) {
            // @todo Maybe could we rename this exception
            throw new InvalidAccessTypeException('Invalid approuvalPrompt, expected either auto or force.');
        }
        $this->approvalPrompt = $prompt;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopesDelimiter()
    {
        return ',';
    }
}
