<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\TestCase\GraphQl;

/**
 * Exception thrown when GraphQL response contains errors.
 */
class ResponseContainsErrorsException extends \Exception
{
    /**
     * @var array
     */
    private $responseData;

    /**
     * @var array
     */
    private $responseHeaders;

    /**
     * @var array
     */
    private $responseCookies;

    /**
     * @param string $message
     * @param array $responseData
     * @param \Exception|null $cause
     * @param int $code
     * @param array $responseHeaders
     * @param array $responseCookies
     */
    public function __construct(
        string $message,
        array $responseData,
        ?\Exception $cause = null,
        int $code = 0,
        array $responseHeaders = [],
        array $responseCookies = []
    ) {
        parent::__construct($message, $code, $cause);
        $this->responseData = $responseData;
        $this->responseHeaders = $responseHeaders;
        $this->responseCookies = $responseCookies;
    }

    /**
     * Get response data
     *
     * @return array
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }

    /**
     * Get response headers
     *
     * @return array
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * Get response cookies
     *
     * @return array
     */
    public function getResponseCookies(): array
    {
        return $this->responseCookies;
    }
}
