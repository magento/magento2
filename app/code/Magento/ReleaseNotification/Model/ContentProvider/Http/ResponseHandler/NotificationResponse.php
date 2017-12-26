<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model\ContentProvider\Http\ResponseHandler;

use Magento\ReleaseNotification\Model\ContentProvider\Http\ResponseHandlerInterface;

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
        return empty($responseBody) ? false : $responseBody;
    }
}
