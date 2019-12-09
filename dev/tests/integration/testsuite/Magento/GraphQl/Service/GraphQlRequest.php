<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Service;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\TestFramework\ObjectManager;
use Magento\Framework\Webapi\Request;

/**
 * Service class to simplify GraphQl requests for integration tests
 */
class GraphQlRequest
{
    /**
     * @var string
     */
    private $controllerPath = '/graphql';

    /**
     * @var Http
     */
    private $httpRequest;

    /**
     * @var array
     */
    private $defaultHeaders = ['Content-Type' => 'application/json'];

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var GraphQl
     */
    private $controller;

    /**
     * @param Http $httpRequest
     * @param SerializerInterface $json
     * @param GraphQl $controller
     */
    public function __construct(
        Http $httpRequest,
        SerializerInterface $json,
        GraphQl $controller
    ) {
        $this->httpRequest = $httpRequest;
        $this->json = $json;
        $this->controller = $controller;
    }

    /**
     * Send request and return response
     *
     * @param string $query
     * @param array $variables
     * @param string $operation
     * @param array $headers
     * @return HttpResponse
     */
    public function send(
        string $query,
        array $variables = [],
        string $operation = '',
        array $headers = []
    ) {
        $this->httpRequest->setPathInfo($this->controllerPath);
        $this->setQuery($query, $variables, $operation)
            ->setHeaders($headers);

        /** @var HttpResponse $response */
        $response = $this->controller->dispatch($this->httpRequest);
        return $response;
    }

    /**
     * Set query data on request
     *
     * @param string $query
     * @param array $variables
     * @param string $operation
     * @return GraphQlRequest
     */
    private function setQuery(string $query, array $variables = [], string $operation = ''): self
    {
        if (strpos(trim($query), 'mutation') === 0) {
            $this->httpRequest->setMethod('POST');
            $this->setPostContent($query, $variables, $operation);
        } else {
            $this->httpRequest->setMethod('GET');
            $this->setGetContent($query, $variables, $operation);
        }

        return $this;
    }

    /**
     * Set headers on request
     *
     * @param array $headers
     * @return GraphQlRequest
     */
    private function setHeaders(array $headers): self
    {
        $allHeaders = array_merge($this->defaultHeaders, $headers);

        $webApiRequest = ObjectManager::getInstance()->get(Request::class);
        $requestHeaders = $webApiRequest->getHeaders();
        foreach ($allHeaders as $key => $value) {
            $requestHeaders->addHeaderLine($key, $value);
        }

        $this->httpRequest->setHeaders($webApiRequest->getHeaders());

        return $this;
    }

    /**
     * Set POST body for request
     *
     * @param string $query
     * @param array $variables
     * @param string $operation
     * @return GraphQlRequest
     */
    private function setPostContent(string $query, array $variables = [], string $operation = ''): self
    {
        $content = [
            'query' => $query,
            'variables' => !empty($variables) ? $this->json->serialize($variables) : null,
            'operationName' => !empty($operation) ? $operation : null
        ];
        $this->httpRequest->setContent($this->json->serialize($content));

        return $this;
    }

    /**
     * Set GET parameters for request
     *
     * @param string $query
     * @param array $variables
     * @param string $operation
     * @return GraphQlRequest
     */
    private function setGetContent(string $query, array $variables = [], string $operation = ''): self
    {
        $this->httpRequest->setQueryValue('query', $query);

        if (!empty($variables)) {
            $this->httpRequest->setQueryValue('variables', $variables);
        }
        if (!empty($operation)) {
            $this->httpRequest->setQueryValue('operationName', $operation);
        }

        return $this;
    }
}
