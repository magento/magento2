<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Debugger;

use Exception;

/**
 * Interface for debugging interaction with Signifyd API
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
