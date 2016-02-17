<?php

/**
 * OAuth service factory.
 *
 * PHP version 5.4
 *
 * @category   OAuth
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2013 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace OAuth;

use OAuth\Common\Service\ServiceInterface;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Client\StreamClient;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Exception\Exception;
use OAuth\OAuth1\Signature\Signature;

class ServiceFactory
{
    /**
     *@var ClientInterface
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $serviceClassMap = array(
        'OAuth1' => array(),
        'OAuth2' => array()
    );

    /**
     * @var array
     */
    protected $serviceBuilders = array(
        'OAuth2' => 'buildV2Service',
        'OAuth1' => 'buildV1Service',
    );

    /**
     * @param ClientInterface $httpClient
     *
     * @return ServiceFactory
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Register a custom service to classname mapping.
     *
     * @param string $serviceName Name of the service
     * @param string $className   Class to instantiate
     *
     * @return ServiceFactory
     *
     * @throws Exception If the class is nonexistent or does not implement a valid ServiceInterface
     */
    public function registerService($serviceName, $className)
    {
        if (!class_exists($className)) {
            throw new Exception(sprintf('Service class %s does not exist.', $className));
        }

        $reflClass = new \ReflectionClass($className);

        foreach (array('OAuth2', 'OAuth1') as $version) {
            if ($reflClass->implementsInterface('OAuth\\' . $version . '\\Service\\ServiceInterface')) {
                $this->serviceClassMap[$version][ucfirst($serviceName)] = $className;

                return $this;
            }
        }

        throw new Exception(sprintf('Service class %s must implement ServiceInterface.', $className));
    }

    /**
     * Builds and returns oauth services
     *
     * It will first try to build an OAuth2 service and if none found it will try to build an OAuth1 service
     *
     * @param string                $serviceName Name of service to create
     * @param CredentialsInterface  $credentials
     * @param TokenStorageInterface $storage
     * @param array|null            $scopes      If creating an oauth2 service, array of scopes
     * @param UriInterface|null     $baseApiUri
     * @param string                $apiVersion version of the api call
     *
     * @return ServiceInterface
     */
    public function createService(
        $serviceName,
        CredentialsInterface $credentials,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null,
        $apiVersion = ""
    ) {
        if (!$this->httpClient) {
            // for backwards compatibility.
            $this->httpClient = new StreamClient();
        }

        foreach ($this->serviceBuilders as $version => $buildMethod) {
            $fullyQualifiedServiceName = $this->getFullyQualifiedServiceName($serviceName, $version);

            if (class_exists($fullyQualifiedServiceName)) {
                return $this->$buildMethod(
                    $fullyQualifiedServiceName,
                    $credentials,
                    $storage,
                    $scopes,
                    $baseApiUri,
                    $apiVersion
                );
            }
        }

        return null;
    }

    /**
     * Gets the fully qualified name of the service
     *
     * @param string $serviceName The name of the service of which to get the fully qualified name
     * @param string $type        The type of the service to get (either OAuth1 or OAuth2)
     *
     * @return string The fully qualified name of the service
     */
    private function getFullyQualifiedServiceName($serviceName, $type)
    {
        $serviceName = ucfirst($serviceName);

        if (isset($this->serviceClassMap[$type][$serviceName])) {
            return $this->serviceClassMap[$type][$serviceName];
        }

        return '\\OAuth\\' . $type . '\\Service\\' . $serviceName;
    }

    /**
     * Builds v2 services
     *
     * @param string                $serviceName The fully qualified service name
     * @param CredentialsInterface  $credentials
     * @param TokenStorageInterface $storage
     * @param array|null            $scopes      Array of scopes for the service
     * @param UriInterface|null     $baseApiUri
     *
     * @return ServiceInterface
     *
     * @throws Exception
     */
    private function buildV2Service(
        $serviceName,
        CredentialsInterface $credentials,
        TokenStorageInterface $storage,
        array $scopes,
        UriInterface $baseApiUri = null,
        $apiVersion = ""
    ) {
        return new $serviceName(
            $credentials,
            $this->httpClient,
            $storage,
            $this->resolveScopes($serviceName, $scopes),
            $baseApiUri,
            $apiVersion
        );
    }

    /**
     * Resolves scopes for v2 services
     *
     * @param string  $serviceName The fully qualified service name
     * @param array   $scopes      List of scopes for the service
     *
     * @return array List of resolved scopes
     */
    private function resolveScopes($serviceName, array $scopes)
    {
        $reflClass = new \ReflectionClass($serviceName);
        $constants = $reflClass->getConstants();

        $resolvedScopes = array();
        foreach ($scopes as $scope) {
            $key = strtoupper('SCOPE_' . $scope);

            if (array_key_exists($key, $constants)) {
                $resolvedScopes[] = $constants[$key];
            } else {
                $resolvedScopes[] = $scope;
            }
        }

        return $resolvedScopes;
    }

    /**
     * Builds v1 services
     *
     * @param string                $serviceName The fully qualified service name
     * @param CredentialsInterface  $credentials
     * @param TokenStorageInterface $storage
     * @param array                 $scopes
     * @param UriInterface          $baseApiUri
     *
     * @return ServiceInterface
     *
     * @throws Exception
     */
    private function buildV1Service(
        $serviceName,
        CredentialsInterface $credentials,
        TokenStorageInterface $storage,
        $scopes,
        UriInterface $baseApiUri = null
    ) {
        if (!empty($scopes)) {
            throw new Exception(
                'Scopes passed to ServiceFactory::createService but an OAuth1 service was requested.'
            );
        }

        return new $serviceName($credentials, $this->httpClient, $storage, new Signature($credentials), $baseApiUri);
    }
}
