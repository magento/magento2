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
    const METHOD_GET = 'GET';

    const METHOD_POST = 'POST';

    const METHOD_HEAD = 'HEAD';

    const METHOD_PUT = 'PUT';

    const METHOD_DELETE = 'DELETE';

    const METHOD_CONNECT = 'CONNECT';

    const METHOD_PATCH = 'PATCH';

    const METHOD_OPTIONS = 'OPTIONS';

    const METHOD_PROPFIND = 'PROPFIND';

    const METHOD_TRACE = 'TRACE';

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
