<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\GraphQl\Exception\ExceptionFormatter;
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\SchemaGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Response;
use Magento\Framework\GraphQl\Query\Fields as QueryFields;

/**
 * Front controller for web API GraphQL area.
 *
 * @api
 * @since 100.3.0
 */
class GraphQl implements FrontControllerInterface
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var SchemaGeneratorInterface
     */
    private $schemaGenerator;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var QueryProcessor
     */
    private $queryProcessor;

    /**
     * @var \Magento\Framework\GraphQl\Exception\ExceptionFormatter
     */
    private $graphQlError;

    /**
     * @var \Magento\Framework\GraphQl\Query\Resolver\ContextInterface
     */
    private $resolverContext;

    /**
     * @var HttpRequestProcessor
     */
    private $requestProcessor;

    /**
     * @var QueryFields
     */
    private $queryFields;

    /**
     * @param Response $response
     * @param SchemaGeneratorInterface $schemaGenerator
     * @param SerializerInterface $jsonSerializer
     * @param QueryProcessor $queryProcessor
     * @param \Magento\Framework\GraphQl\Exception\ExceptionFormatter $graphQlError
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $resolverContext
     * @param HttpRequestProcessor $requestProcessor
     * @param QueryFields $queryFields
     */
    public function __construct(
        Response $response,
        SchemaGeneratorInterface $schemaGenerator,
        SerializerInterface $jsonSerializer,
        QueryProcessor $queryProcessor,
        ExceptionFormatter $graphQlError,
        ContextInterface $resolverContext,
        HttpRequestProcessor $requestProcessor,
        QueryFields $queryFields
    ) {
        $this->response = $response;
        $this->schemaGenerator = $schemaGenerator;
        $this->jsonSerializer = $jsonSerializer;
        $this->queryProcessor = $queryProcessor;
        $this->graphQlError = $graphQlError;
        $this->resolverContext = $resolverContext;
        $this->requestProcessor = $requestProcessor;
        $this->queryFields = $queryFields;
    }

    /**
     * Handle GraphQL request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @since 100.3.0
     */
    public function dispatch(RequestInterface $request) : ResponseInterface
    {
        $statusCode = 200;
        try {
            /** @var Http $request */
            $this->requestProcessor->processHeaders($request);
            $data = $this->jsonSerializer->unserialize($request->getContent());

            $query = isset($data['query']) ? $data['query'] : '';
            $variables = isset($data['variables']) ? $data['variables'] : null;
            // We have to extract queried field names to avoid instantiation of non necessary fields in webonyx schema
            // Temporal coupling is required for performance optimization
            $this->queryFields->setQuery($query, $variables);
            $schema = $this->schemaGenerator->generate();

            $result = $this->queryProcessor->process(
                $schema,
                $query,
                $this->resolverContext,
                isset($data['variables']) ? $data['variables'] : []
            );
        } catch (\Exception $error) {
            $result['errors'] = isset($result) && isset($result['errors']) ? $result['errors'] : [];
            $result['errors'][] = $this->graphQlError->create($error);
            $statusCode = ExceptionFormatter::HTTP_GRAPH_QL_SCHEMA_ERROR_STATUS;
        }
        $this->response->setBody($this->jsonSerializer->serialize($result))->setHeader(
            'Content-Type',
            'application/json'
        )->setHttpResponseCode($statusCode);
        return $this->response;
    }
}
