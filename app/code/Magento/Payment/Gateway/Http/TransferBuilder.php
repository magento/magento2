<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http;

/**
 * Class TransferBuilder
 * @api
 * @since 2.0.0
 */
class TransferBuilder
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $clientConfig = [];

    /**
     * @var array
     * @since 2.0.0
     */
    private $headers = [];

    /**
     * @var string
     * @since 2.0.0
     */
    private $method;

    /**
     * @var array|string
     * @since 2.0.0
     */
    private $body = [];

    /**
     * @var string
     * @since 2.0.0
     */
    private $uri = '';

    /**
     * @var bool
     * @since 2.0.0
     */
    private $encode = false;

    /**
     * @var array
     * @since 2.0.0
     */
    private $auth = [Transfer::AUTH_USERNAME => null, Transfer::AUTH_PASSWORD => null];

    /**
     * @param array $clientConfig
     * @return $this
     * @since 2.0.0
     */
    public function setClientConfig(array $clientConfig)
    {
        $this->clientConfig = $clientConfig;

        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     * @since 2.0.0
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param array|string $body
     * @return $this
     * @since 2.0.0
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param string $username
     * @return $this
     * @since 2.0.0
     */
    public function setAuthUsername($username)
    {
        $this->auth[Transfer::AUTH_USERNAME] = $username;

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     * @since 2.0.0
     */
    public function setAuthPassword($password)
    {
        $this->auth[Transfer::AUTH_PASSWORD] = $password;

        return $this;
    }

    /**
     * @param string $method
     * @return $this
     * @since 2.0.0
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param string $uri
     * @return $this
     * @since 2.0.0
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @param bool $encode
     * @return $this
     * @since 2.0.0
     */
    public function shouldEncode($encode)
    {
        $this->encode = $encode;

        return $this;
    }

    /**
     * @return TransferInterface
     * @since 2.0.0
     */
    public function build()
    {
        return new Transfer(
            $this->clientConfig,
            $this->headers,
            $this->body,
            $this->auth,
            $this->method,
            $this->uri,
            $this->encode
        );
    }
}
