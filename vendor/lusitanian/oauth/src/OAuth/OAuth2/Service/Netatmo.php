<?php
/**
 * Netatmo service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://dev.netatmo.com/doc/
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
 * Netatmo service.
 *
 * @author  Pedro Amorim <contact@pamorim.fr>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link    https://dev.netatmo.com/doc/
 */
class Netatmo extends AbstractService
{

    // SCOPES
    // @link https://dev.netatmo.com/doc/authentication/scopes

    // Used to read weather station's data (devicelist, getmeasure)
    const SCOPE_STATION_READ        = 'read_station';
    // Used to read thermostat's data (devicelist, getmeasure, getthermstate)
    const SCOPE_THERMOSTAT_READ     = 'read_thermostat';
    // Used to configure the thermostat (syncschedule, setthermpoint)
    const SCOPE_THERMOSTAT_WRITE    = 'write_thermostat';

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
            true // use parameter state
        );

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://api.netatmo.net/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri($this->baseApiUri.'oauth2/authorize');

    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri($this->baseApiUri.'oauth2/token');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_QUERY_STRING;
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
}
