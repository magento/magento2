<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

use Laminas\Http\Response;

/**
 * Extract result from http response. Call response handler by status.
 */
class ResponseResolver
{
    /**
     * @var ConverterInterface
     */
    private ConverterInterface $converter;

    /**
     * @var array
     */
    private array $responseHandlers;

    /**
     * @param ConverterInterface $converter
     * @param ResponseHandlerInterface[] $responseHandlers
     */
    public function __construct(ConverterInterface $converter, array $responseHandlers = [])
    {
        $this->converter = $converter;
        $this->responseHandlers = $responseHandlers;
    }

    /**
     * Get result from $response.
     *
     * @param Response $response
     * @return bool|string
     */
    public function getResult(Response $response)
    {
        $result = false;
        $converterMediaType = $this->converter->getContentMediaType();

        /** Content-Type header may not only contain media-type declaration */
        $responseBody = $response->getBody();
        $contentType = $response->getHeaders()->has('Content-Type') ?
            $response->getHeaders()->get('Content-Type')->getFieldValue() :
            '';
        if ($responseBody && is_int(strripos($contentType, $converterMediaType))) {
            $responseBody = $this->converter->fromBody($responseBody);
        } else {
            $responseBody = [];
        }

        if (array_key_exists($response->getStatusCode(), $this->responseHandlers)) {
            $result = $this->responseHandlers[$response->getStatusCode()]->handleResponse($responseBody);
        }

        return $result;
    }
}
