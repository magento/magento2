<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\AsyncClient;

/**
 * Request to send.
 */
class Request
{
    public const METHOD_GET = 'GET';

    public const METHOD_POST = 'POST';

    public const METHOD_HEAD = 'HEAD';

    public const METHOD_PUT = 'PUT';

    public const METHOD_DELETE = 'DELETE';

    public const METHOD_CONNECT = 'CONNECT';

    public const METHOD_PATCH = 'PATCH';

    public const METHOD_OPTIONS = 'OPTIONS';

    public const METHOD_PROPFIND = 'PROPFIND';

    public const METHOD_TRACE = 'TRACE';

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string[]
     */
    private $headers;

    /**
     * @var string|null
     */
    private $body;

    /**
     * @param string $url
     * @param string $method
     * @param string[] $headers
     * @param string $body
     */
    public function __construct(string $url, string $method, array $headers, ?string $body)
    {
        $this->url = $url;
        $this->method = $method;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * URL to send request to.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * HTTP method to use.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Headers to send.
     *
     * Keys - header names, values - array of header values.
     *
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Body to send
     *
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }
}
