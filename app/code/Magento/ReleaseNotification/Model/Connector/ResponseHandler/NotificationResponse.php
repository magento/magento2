<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model\Connector\ResponseHandler;

use Magento\ReleaseNotification\Model\Connector\ResponseHandlerInterface;

/**
 * Class NotificationResponse
 *
 * Retrieves release notification data from the response body
 */
class NotificationResponse implements ResponseHandlerInterface
{
    /**
     * @param array $responseBody
     *
     * @return array|false
     */
    public function handleResponse(array $responseBody)
    {
        return !empty($responseBody) ? $responseBody : false;
    }
}
