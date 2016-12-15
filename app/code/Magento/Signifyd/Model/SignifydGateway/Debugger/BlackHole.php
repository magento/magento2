<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Debugger;

use Exception;

/**
 * This debugger ignores any information.
 * Optimal production environment.
 */
class BlackHole implements DebuggerInterface
{
    /**
     * {@inheritdoc}
     */
    public function success($requestUrl, $requestData, $responseStatus, $responseBody)
    {
        // ignore
    }

    /**
     * {@inheritdoc}
     */
    public function failure($requestUrl, $requestData, Exception $exception)
    {
        // ignore
    }
}
