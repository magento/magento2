<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http;

class Transfer implements TransferInterface
{
    /**
     * @var array
     */
    private $clientConfig;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $body;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var bool
     */
    private $encode;

    /**
     * @param array $clientConfig
     * @param array $headers
     * @param array $body
     * @param string $method
     * @param string $uri
     * @param bool $encode
     */
    public function __construct(
        array $clientConfig,
        array $headers,
        array $body,
        $method,
        $uri,
        $encode = false
    ) {
        $this->clientConfig = $clientConfig;
        $this->headers = $headers;
        $this->method = $method;
        $this->body = $body;
        $this->uri = $uri;
        $this->encode = $encode;
    }

    /**
     * Returns gateway client configuration
     *
     * @return array
     */
    public function getClientConfig()
    {
        return $this->clientConfig;
    }

    /**
     * Returns method used to place request
     *
     * @return string|int
     */
    public function getMethod()
    {
        return (string)$this->method;
    }

    /**
     * Returns headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Whether body should be encoded before place
     *
     * @return bool
     */
    public function shouldEncode()
    {
        return (bool)$this->encode;
    }

    /**
     * Returns request body
     *
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns URI
     *
     * @return string
     */
    public function getUri()
    {
        return (string)$this->uri;
    }
}
