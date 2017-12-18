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
     * @inheritdoc
     */
    public function handleResponse($responseBody)
    {
        return !empty($responseBody) ? $responseBody : false;
    }
}
