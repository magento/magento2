<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Language\Visitor;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\GraphQl\Exception\ExceptionFormatter;
use Magento\Framework\GraphQl\Query\Fields as QueryFields;
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\SchemaGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Response;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQl\Model\Query\Logger\LoggerInterface;
use Magento\GraphQl\Model\Query\Logger\LoggerPool;

/**
 * Front controller for web API GraphQL area.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.3.0
 */
class GraphQl implements FrontControllerInterface
{
    /**
     * @var \Magento\Framework\Webapi\Response
     * @deprecated 100.3.2
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
     * @deprecated 100.3.3 $contextFactory is used for creating Context object
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
     * @var LoggerPool
     */
    private $loggerPool;

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
     * @param ContextFactoryInterface $contextFactory
     * @param LoggerPool $loggerPool
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
        LoggerPool $loggerPool = null
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
        $this->loggerPool = $loggerPool ?: ObjectManager::getInstance()->get(LoggerPool::class);
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
        $statusCode = 200;
        $jsonResult = $this->jsonFactory->create();
        try {
            /** @var Http $request */
            $this->requestProcessor->validateRequest($request);

            $data = $this->getDataFromRequest($request);
            $query = $data['query'] ?? '';
            $variables = $data['variables'] ?? null;

            // We must extract queried field names to avoid instantiation of unnecessary fields in webonyx schema
            // Temporal coupling is required for performance optimization
            $this->queryFields->setQuery($query, $variables);

            // log information about the query
            $this->logQueryInformation($request, $data);

            $schema = $this->schemaGenerator->generate();

            $result = $this->queryProcessor->process(
                $schema,
                $query,
                $this->contextFactory->create(),
                $data['variables'] ?? []
            );
        } catch (\Exception $error) {
            $result['errors'] = isset($result) && isset($result['errors']) ? $result['errors'] : [];
            $result['errors'][] = $this->graphQlError->create($error);
            $statusCode = ExceptionFormatter::HTTP_GRAPH_QL_SCHEMA_ERROR_STATUS;
        }

        $jsonResult->setHttpResponseCode($statusCode);
        $jsonResult->setData($result);
        $jsonResult->renderResult($this->httpResponse);
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
        if ($request->isPost()) {
            $data = $this->jsonSerializer->unserialize($request->getContent());
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

    /**
     * Logs information about the query
     *
     * @param RequestInterface $request
     * @param array $data
     * @throws SyntaxError
     */
    private function logQueryInformation(RequestInterface $request, array $data)
    {
        $query = $data['query'] ?? '';
        $queryInformation = [];
        $queryInformation[LoggerInterface::HTTP_METHOD] = $request->getMethod();
        $queryInformation[LoggerInterface::STORE_HEADER] = $request->getHeader('Store', '');
        $queryInformation[LoggerInterface::CURRENCY_HEADER] = $request->getHeader('Currency', '');
        $queryInformation[LoggerInterface::AUTH_HEADER_SET] = $request->getHeader('Authorization') ? 'true' : 'false';
        $queryInformation[LoggerInterface::IS_CACHEABLE] = $request->getHeader('X-Magento-Cache-Id') ? 'true' : 'false';
        $queryInformation[LoggerInterface::NUMBER_OF_QUERIES] = '';
        $queryInformation[LoggerInterface::QUERY_NAMES] = $this->getOperationName($data);
        $queryInformation[LoggerInterface::HAS_MUTATION] = str_contains($query, 'mutation') ? 'true' : 'false';
        $queryInformation[LoggerInterface::QUERY_COMPLEXITY] = $this->getFieldCount($query);
        $queryInformation[LoggerInterface::QUERY_LENGTH] = $request->getHeader('Content-Length');

        $this->loggerPool->execute($queryInformation);
    }

    /**
     * Get GraphQL query operation name
     *
     * @param array $data
     * @return string
     */
    private function getOperationName(array $data): string
    {
        if (isset($data['operationName']) && is_string($data['operationName']) && $data['operationName'] !== '') {
            return $data['operationName'];
        }

        $fields = $this->queryFields->getFieldsUsedInQuery();
        return current($fields) ?: 'operationNameNotFound';
    }

    /**
     * Gets the field count
     *
     * @param string $query
     * @return int
     * @throws SyntaxError
     * @throws \Exception
     */
    private function getFieldCount(string $query): int
    {
        if (!empty($query)) {
            $totalFieldCount = 0;
            $queryAst = Parser::parse(new Source($query ?: '', 'GraphQL'));
            Visitor::visit(
                $queryAst,
                [
                    'leave' => [
                        NodeKind::FIELD => function (Node $node) use (&$totalFieldCount) {
                            $totalFieldCount++;
                        }
                    ]
                ]
            );
            return $totalFieldCount;
        }
        return 0;
    }
}
