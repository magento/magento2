<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http;

/**
 * Class Transfer
 * @since 2.0.0
 */
class Transfer implements TransferInterface
{
    /**
     * Name of Auth username field
     */
    const AUTH_USERNAME = 'username';

    /**
     * Name of Auth password field
     */
    const AUTH_PASSWORD = 'password';

    /**
     * @var array
     * @since 2.0.0
     */
    private $clientConfig;

    /**
     * @var array
     * @since 2.0.0
     */
    private $headers;

    /**
     * @var string
     * @since 2.0.0
     */
    private $method;

    /**
     * @var array|string
     * @since 2.0.0
     */
    private $body;

    /**
     * @var string
     * @since 2.0.0
     */
    private $uri;

    /**
     * @var bool
     * @since 2.0.0
     */
    private $encode;

    /**
     * @var array
     * @since 2.0.0
     */
    private $auth;

    /**
     * @param array $clientConfig
     * @param array $headers
     * @param array|string $body
     * @param array $auth
     * @param string $method
     * @param string $uri
     * @param bool $encode
     * @since 2.0.0
     */
    public function __construct(
        array $clientConfig,
        array $headers,
        $body,
        array $auth,
        $method,
        $uri,
        $encode
    ) {
        $this->clientConfig = $clientConfig;
        $this->headers = $headers;
        $this->body = $body;
        $this->auth = $auth;
        $this->method = $method;
        $this->uri = $uri;
        $this->encode = $encode;
    }

    /**
     * Returns gateway client configuration
     *
     * @return array
     * @since 2.0.0
     */
    public function getClientConfig()
    {
        return $this->clientConfig;
    }

    /**
     * Returns method used to place request
     *
     * @return string|int
     * @since 2.0.0
     */
    public function getMethod()
    {
        return (string)$this->method;
    }

    /**
     * Returns headers
     *
     * @return array
     * @since 2.0.0
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns request body
     *
     * @return array|string
     * @since 2.0.0
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns URI
     *
     * @return string
     * @since 2.0.0
     */
    public function getUri()
    {
        return (string)$this->uri;
    }

    /**
     * @return boolean
     * @since 2.0.0
     */
    public function shouldEncode()
    {
        return $this->encode;
    }

    /**
     * Returns Auth username
     *
     * @return string
     * @since 2.0.0
     */
    public function getAuthUsername()
    {
        return $this->auth[self::AUTH_USERNAME];
    }

    /**
     * Returns Auth password
     *
     * @return string
     * @since 2.0.0
     */
    public function getAuthPassword()
    {
        return $this->auth[self::AUTH_PASSWORD];
    }
}
