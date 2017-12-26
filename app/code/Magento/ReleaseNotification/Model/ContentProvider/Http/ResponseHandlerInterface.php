<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model\ContentProvider\Http;

/**
 * Class ResponseHandlerInterface
 *
 * Represents an interface for response handler which process response body.
 */
interface ResponseHandlerInterface
{
    /**
     * @param string $responseBody
     * @return bool|string
     */
    public function handleResponse($responseBody);
}
