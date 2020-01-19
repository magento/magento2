<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\TestCase\GraphQl;

/**
 * Response contains errors exception
 */
class ResponseContainsErrorsException extends \Exception
{
    /**
     * @var array
     */
    private $responseData;

    /**
     * @param string $message
     * @param array $responseData
     * @param \Exception|null $cause
     * @param int $code
     */
    public function __construct(string $message, array $responseData, \Exception $cause = null, int $code = 0)
    {
        parent::__construct($message, $code, $cause);
        $this->responseData = $responseData;
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
}
