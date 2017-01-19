<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function success($requestUrl, $requestData, $responseStatus, $responseBody)
    {
        // ignore
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function failure($requestUrl, $requestData, Exception $exception)
    {
        // ignore
    }
}
