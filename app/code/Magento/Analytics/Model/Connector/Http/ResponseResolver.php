<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Extract result from http response. Call response handler by status.
 * @since 2.2.0
 */
class ResponseResolver
{
    /**
     * @var ConverterInterface
     * @since 2.2.0
     */
    private $converter;

    /**
     * @var array
     * @since 2.2.0
     */
    private $responseHandlers;

    /**
     * @param ConverterInterface $converter
     * @param ResponseHandlerInterface[] $responseHandlers
     * @since 2.2.0
     */
    public function __construct(ConverterInterface $converter, array $responseHandlers = [])
    {
        $this->converter = $converter;
        $this->responseHandlers = $responseHandlers;
    }

    /**
     * @param \Zend_Http_Response $response
     *
     * @return bool|string
     * @since 2.2.0
     */
    public function getResult(\Zend_Http_Response $response)
    {
        $result = false;
        $responseBody = $this->converter->fromBody($response->getBody());
        if (array_key_exists($response->getStatus(), $this->responseHandlers)) {
            $result = $this->responseHandlers[$response->getStatus()]->handleResponse($responseBody);
        }

        return $result;
    }
}
