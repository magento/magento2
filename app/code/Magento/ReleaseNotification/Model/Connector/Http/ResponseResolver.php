<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model\Connector\Http;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\ReleaseNotification\Model\Connector\ResponseHandlerInterface;

/**
 * Class ResponseResolver
 *
 * Extract result from http response. Call response handler by status.
 */
class ResponseResolver
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $responseHandlers;

    /**
     * @param SerializerInterface $serializer
     * @param ResponseHandlerInterface[] $responseHandlers
     */
    public function __construct(
        SerializerInterface $serializer,
        array $responseHandlers = []
    ) {
        $this->serializer = $serializer;
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
        $responseBody = $this->serializer->unserialize($response);
        if (array_key_exists($status, $this->responseHandlers)) {
            $result = $this->responseHandlers[$status]->handleResponse($responseBody);
        }

        return $result;
    }
}
