<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\GraphQl\Exception\ExceptionFormatter;
use Magento\Framework\GraphQl\Exception\GraphQlRequestException;
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\SchemaGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\GraphQl\Query\Fields as QueryFields;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Front controller for web API GraphQL area.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GraphQl implements FrontControllerInterface
{
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
     * @var QueryFields
     */
    private $queryFields;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @param SchemaGeneratorInterface $schemaGenerator
     * @param SerializerInterface $jsonSerializer
     * @param QueryProcessor $queryProcessor
     * @param \Magento\Framework\GraphQl\Exception\ExceptionFormatter $graphQlError
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $resolverContext
     * @param QueryFields $queryFields
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        SchemaGeneratorInterface $schemaGenerator,
        SerializerInterface $jsonSerializer,
        QueryProcessor $queryProcessor,
        ExceptionFormatter $graphQlError,
        ContextInterface $resolverContext,
        QueryFields $queryFields,
        JsonFactory $jsonFactory
    ) {
        $this->schemaGenerator = $schemaGenerator;
        $this->jsonSerializer = $jsonSerializer;
        $this->queryProcessor = $queryProcessor;
        $this->graphQlError = $graphQlError;
        $this->resolverContext = $resolverContext;
        $this->queryFields = $queryFields;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Handle GraphQL request
     *
     * @param RequestInterface $request
     * @return ResponseInterface|ResultInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $statusCode = 200;
        $jsonResult = $this->jsonFactory->create();
        try {
            /** @var HttpRequest $request */
            if ($this->isHttpVerbValid($request)) {
                $data = $this->getDataFromRequest($request);
                $query = isset($data['query']) ? $data['query'] : '';
                $variables = isset($data['variables']) ? $data['variables'] : null;

                // We must extract queried field names to avoid instantiation of unnecessary fields in webonyx schema
                // Temporal coupling is required for performance optimization
                $this->queryFields->setQuery($query, $variables);
                $schema = $this->schemaGenerator->generate();

                $result = $this->queryProcessor->process(
                    $schema,
                    $query,
                    $this->resolverContext,
                    isset($data['variables']) ? $data['variables'] : []
                );
            } else {
                $errorMessage = __('Mutation requests allowed only for POST requests');
                $result['errors'] = [
                    $this->graphQlError->create(new GraphQlRequestException($errorMessage))
                ];
                $statusCode = ExceptionFormatter::HTTP_GRAPH_QL_SCHEMA_ERROR_STATUS;
            }
        } catch (\Exception $error) {
            $result['errors'] = isset($result) && isset($result['errors']) ? $result['errors'] : [];
            $result['errors'][] = $this->graphQlError->create($error);
            $statusCode = ExceptionFormatter::HTTP_GRAPH_QL_SCHEMA_ERROR_STATUS;
        }

        $jsonResult->setHttpResponseCode($statusCode);
        $jsonResult->setData($result);
        return $jsonResult;
    }

    /**
     * Get data from request body or query string
     *
     * @param HttpRequest $request
     * @return array
     */
    private function getDataFromRequest(HttpRequest $request) : array
    {
        if ($request->isPost()) {
            $data = $this->jsonSerializer->unserialize($request->getContent());
        } else {
            $data = $request->getParams();
            $data['variables'] = isset($data['variables']) ?
                $this->jsonSerializer->unserialize($data['variables']) : null;
        }

        return $data;
    }

    /**
     * Check if request is using correct verb for query or mutation
     *
     * @param HttpRequest $request
     * @return boolean
     */
    private function isHttpVerbValid(HttpRequest $request)
    {
        $requestData = $this->getDataFromRequest($request);
        $query = $requestData['query'] ?? '';

        // The easiest way to determine mutations without additional parsing
        if ($request->isSafeMethod() && strpos(trim($query), 'mutation') === 0) {
            return false;
        }
        return true;
    }
}
