<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\AsyncClient;

/**
 * Http response.
 *
 * @api
 */
class Response
{
    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string[]
     */
    private $headers;

    /**
     * @var string
     */
    private $body;

    /**
     * @param int $statusCode
     * @param string[] $headers
     * @param string $body
     */
    public function __construct(int $statusCode, array $headers, string $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = array_change_key_case($headers, CASE_LOWER);
        $this->body = $body;
    }

    /**
     * Status code returned.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * With header names as keys (case preserved) and values as header values.
     *
     * If a header's value had multiple values they will be shown like "val1, val2, val3".
     * Header names are all lower-case.
     *
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Response body.
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
}
