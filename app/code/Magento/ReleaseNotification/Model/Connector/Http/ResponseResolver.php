<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model\Connector\Http;

use Magento\ReleaseNotification\Model\Connector\ResponseHandlerInterface;

/**
 * Class ResponseResolver
 *
 * Extract result from http response. Call response handler by status.
 */
class ResponseResolver
{

    /**
     * @var array
     */
    private $responseHandlers;

    /**
     * @param ResponseHandlerInterface[] $responseHandlers
     */
    public function __construct(
        array $responseHandlers = []
    ) {
        $this->responseHandlers = $responseHandlers;
    }

    /**
     * @param string $response
     * @param int $status
     * @return bool|string
     */
    public function getResult($response, $status)
    {
        $result = false;
        if (array_key_exists($status, $this->responseHandlers)) {
            $result = $this->responseHandlers[$status]->handleResponse($response);
        }

        return $result;
    }
}
