<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Debugger;

use Exception;

/**
 * Interface for debugging interaction with Signifyd API
 * @since 2.2.0
 */
interface DebuggerInterface
{
    /**
     * Register debug information about accepted request to Signifyd API
     *
     * @param string $requestUrl
     * @param string $requestData
     * @param string $responseStatus
     * @param string $responseBody
     * @return void
     * @since 2.2.0
     */
    public function success($requestUrl, $requestData, $responseStatus, $responseBody);

    /**
     * Register debug information about failed request to Signifyd API
     *
     * @param string $requestUrl
     * @param string $requestData
     * @param Exception $exception
     * @return mixed
     * @since 2.2.0
     */
    public function failure($requestUrl, $requestData, Exception $exception);
}
