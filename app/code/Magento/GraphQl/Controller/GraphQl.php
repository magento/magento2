<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Controller;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\Source;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\GraphQl\Exception\ExceptionFormatter;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Fields as QueryFields;
use Magento\Framework\GraphQl\Query\QueryParser;
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\SchemaGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Response;
use Magento\GraphQl\Helper\Query\Logger\LogData;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQl\Model\Query\Logger\LoggerPool;
use Magento\GraphQl\Model\GraphQl\RequestConfiguration;

/**
 * Front controller for web API GraphQL area.
 *
 * Default max POST body size is defined on {@see RequestConfiguration::DEFAULT_MAX_REQUEST_BODY_SIZE}.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @since 100.3.0
 */
class GraphQl implements FrontControllerInterface
{
    /**
     * @var \Magento\Framework\Webapi\Response
     * @deprecated 100.3.2
     * @see no replacement
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
     * @var ExceptionFormatter
     */
    private $graphQlError;

    /**
     * @var ContextInterface
     * @deprecated 100.3.3
     * @see $contextFactory is used for creating Context object
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
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var HttpResponse
     */
    private $httpResponse;

    /**
     * @var ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @var LogData
     */
    private $logDataHelper;

    /**
     * @var LoggerPool
     */
    private $loggerPool;

    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var QueryParser
     */
    private $queryParser;

    /**
     * @var int
     */
    private int $maxRequestBodySize;

    /**
     * @param Response $response
     * @param SchemaGeneratorInterface $schemaGenerator
     * @param SerializerInterface $jsonSerializer
     * @param QueryProcessor $queryProcessor
     * @param ExceptionFormatter $graphQlError
     * @param ContextInterface $resolverContext Deprecated. $contextFactory is used for creating Context object.
     * @param HttpRequestProcessor $requestProcessor
     * @param QueryFields $queryFields
     * @param JsonFactory|null $jsonFactory
     * @param HttpResponse|null $httpResponse
     * @param ContextFactoryInterface|null $contextFactory
     * @param LogData|null $logDataHelper
     * @param LoggerPool|null $loggerPool
     * @param AreaList|null $areaList
     * @param QueryParser|null $queryParser
     * @param RequestConfiguration|null $requestConfiguration
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Response $response,
        SchemaGeneratorInterface $schemaGenerator,
        SerializerInterface $jsonSerializer,
        QueryProcessor $queryProcessor,
        ExceptionFormatter $graphQlError,
        ContextInterface $resolverContext,
        HttpRequestProcessor $requestProcessor,
        QueryFields $queryFields,
        JsonFactory $jsonFactory = null,
        HttpResponse $httpResponse = null,
        ContextFactoryInterface $contextFactory = null,
        LogData $logDataHelper = null,
        LoggerPool $loggerPool = null,
        AreaList $areaList = null,
        QueryParser $queryParser = null,
        ?RequestConfiguration $requestConfiguration = null
    ) {
        $this->response = $response;
        $this->schemaGenerator = $schemaGenerator;
        $this->jsonSerializer = $jsonSerializer;
        $this->queryProcessor = $queryProcessor;
        $this->graphQlError = $graphQlError;
        $this->resolverContext = $resolverContext;
        $this->requestProcessor = $requestProcessor;
        $this->queryFields = $queryFields;
        $this->jsonFactory = $jsonFactory ?: ObjectManager::getInstance()->get(JsonFactory::class);
        $this->httpResponse = $httpResponse ?: ObjectManager::getInstance()->get(HttpResponse::class);
        $this->contextFactory = $contextFactory ?: ObjectManager::getInstance()->get(ContextFactoryInterface::class);
        $this->logDataHelper = $logDataHelper ?: ObjectManager::getInstance()->get(LogData::class);
        $this->loggerPool = $loggerPool ?: ObjectManager::getInstance()->get(LoggerPool::class);
        $this->areaList = $areaList ?: ObjectManager::getInstance()->get(AreaList::class);
        $this->queryParser = $queryParser ?: ObjectManager::getInstance()->get(QueryParser::class);
        $requestConfiguration = $requestConfiguration
            ?: ObjectManager::getInstance()->get(RequestConfiguration::class);
        $this->maxRequestBodySize = $requestConfiguration->getMaxRequestBodySize();
    }

    /**
     * Handle GraphQL request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @since 100.3.0
     */
    public function dispatch(RequestInterface $request): ResponseInterface
    {
        $this->areaList->getArea(Area::AREA_GRAPHQL)->load(Area::PART_TRANSLATE);

        $statusCode = 200;
        $jsonResult = $this->jsonFactory->create();
        $data = [];
        $result = [];

        $schema = null;
        try {
            $data = $this->getDataFromRequest($request);
            /** @var Http $request */
            $this->requestProcessor->validateRequest($request);
            $query = $data['query'] ?? '';
            $parsedQuery = $this->queryParser->parse($query);
            $data['parsedQuery'] = $parsedQuery;

            // We must extract queried field names to avoid instantiation of unnecessary fields in webonyx schema
            // Temporal coupling is required for performance optimization
            $this->queryFields->setQuery($parsedQuery, $data['variables'] ?? null);
            $schema = $this->schemaGenerator->generate();

            $result = $this->queryProcessor->process(
                $schema,
                $parsedQuery,
                $this->contextFactory->create(),
                $data['variables'] ?? []
            );
        } catch (\Exception $error) {
            $result['errors'] = isset($result['errors']) ? $result['errors'] : [];
            $result['errors'][] = $this->graphQlError->create($error);
            $statusCode = ExceptionFormatter::HTTP_GRAPH_QL_SCHEMA_ERROR_STATUS;
        }

        $jsonResult->setHttpResponseCode($statusCode);
        $jsonResult->setData($result);
        $jsonResult->renderResult($this->httpResponse);

        // log information about the query, unless it is an introspection query
        if (!isset($data['query']) || strpos($data['query'], 'IntrospectionQuery') === false) {
            $queryInformation = $this->logDataHelper->getLogData($request, $data, $schema, $this->httpResponse);
            $this->loggerPool->execute($queryInformation);
        }

        return $this->httpResponse;
    }

    /**
     * Get data from request body or query string
     *
     * @param RequestInterface $request
     * @return array
     */
    private function getDataFromRequest(RequestInterface $request): array
    {
        /** @var Http $request */
        if ($request->isPost() && $request->getContent()) {
            $content = $request->getContent();
            if ($this->maxRequestBodySize > 0 && strlen($content) > $this->maxRequestBodySize) {
                throw new GraphQlInputException(
                    __('Request body is too large.')
                );
            }
            try {
                $data = $this->jsonSerializer->unserialize($content);
            } catch (\InvalidArgumentException) {
                $source = new Source($content);
                throw new SyntaxError($source, 0, 'Unable to parse the request.');
            }
        } elseif ($request->isGet()) {
            $data = $request->getParams();
            $data['variables'] = isset($data['variables']) ?
                $this->jsonSerializer->unserialize($data['variables']) : null;
            $data['variables'] = is_array($data['variables']) ?
                $data['variables'] : null;
        } else {
            return [];
        }

        return $data;
    }
}
