<?php
/**
 * ParrotFlowerPower service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://flowerpowerdev.parrot.com/projects/flower-power-web-service-api/wiki
 */

namespace OAuth\OAuth2\Service;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\OAuth2\Service\Exception\MissingRefreshTokenException;
use OAuth\Common\Token\TokenInterface;

/**
 * ParrotFlowerPower service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://flowerpowerdev.parrot.com/projects/flower-power-web-service-api/wiki
 */
class ParrotFlowerPower extends AbstractService
{

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
            $this->baseApiUri = new Uri('https://apiflowerpower.parrot.com/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri($this->baseApiUri.'oauth2/v1/authorize');

    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri($this->baseApiUri.'user/v1/authenticate');
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
        } elseif (isset($data['error'])) {
            throw new TokenResponseException(
                'Error in retrieving token: "' . $data['error'] . '"'
            );
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);
        $token->setLifetime($data['expires_in']);

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);
        unset($data['expires_in']);

        $token->setExtraParams($data);

        return $token;
    }


    /**
     * Parrot use a different endpoint for refresh a token
     *
     * {@inheritdoc}
     */
    public function refreshAccessToken(TokenInterface $token)
    {
        $refreshToken = $token->getRefreshToken();

        if (empty($refreshToken)) {
            throw new MissingRefreshTokenException();
        }

        $parameters = array(
            'grant_type'    => 'refresh_token',
            'type'          => 'web_server',
            'client_id'     => $this->credentials->getConsumerId(),
            'client_secret' => $this->credentials->getConsumerSecret(),
            'refresh_token' => $refreshToken,
        );

        $responseBody = $this->httpClient->retrieveResponse(
            new Uri($this->baseApiUri.'user/v1/refresh'),
            $parameters,
            $this->getExtraOAuthHeaders()
        );
        $token = $this->parseAccessTokenResponse($responseBody);
        $this->storage->storeAccessToken($this->service(), $token);

        return $token;
    }
}
