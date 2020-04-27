<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Debugger;

use Exception;

/**
 * Interface for debugging interaction with Signifyd API
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
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
     */
    public function success($requestUrl, $requestData, $responseStatus, $responseBody);

    /**
     * Register debug information about failed request to Signifyd API
     *
     * @param string $requestUrl
     * @param string $requestData
     * @param Exception $exception
     * @return mixed
     */
    public function failure($requestUrl, $requestData, Exception $exception);
}
