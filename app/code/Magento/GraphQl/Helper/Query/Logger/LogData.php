<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Helper\Query\Logger;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Language\Visitor;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\GraphQl\Schema;
use Magento\GraphQl\Model\Query\Logger\LoggerInterface;

/**
 * Helper class to collect data for logging GraphQl requests
 */
class LogData
{
    /**
     * Extracts relevant information about the request
     *
     * @param RequestInterface $request
     * @param array $data
     * @param Schema|null $schema
     * @param HttpResponse|null $response
     * @return array
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function getLogData(
        RequestInterface $request,
        array $data,
        ?Schema $schema,
        ?HttpResponse $response
    ) : array {
        $logData = [];
        $logData = array_merge($logData, $this->gatherRequestInformation($request));

        try {
            $complexity = $this->getFieldCount($data['query'] ?? '');
            $logData[LoggerInterface::COMPLEXITY] = $complexity;
            if ($schema) {
                $logData = array_merge($logData, $this->gatherQueryInformation($schema));
            }
        } catch (\Exception $exception) {}  //@codingStandardsIgnoreLine

        if ($response) {
            $logData = array_merge($logData, $this->gatherResponseInformation($response));
        }

        return $logData;
    }

    /**
     * Gets the information needed from the request
     *
     * @param RequestInterface $request
     * @return array
     */
    private function gatherRequestInformation(RequestInterface $request) : array
    {
        $requestInformation[LoggerInterface::HTTP_METHOD] = $request->getMethod();
        $requestInformation[LoggerInterface::STORE_HEADER] = $request->getHeader('Store') ?: '';
        $requestInformation[LoggerInterface::CURRENCY_HEADER] = $request->getHeader('Currency') ?: '';
        $requestInformation[LoggerInterface::HAS_AUTH_HEADER] = $request->getHeader('Authorization') ? 'true' : 'false';
        $requestInformation[LoggerInterface::REQUEST_LENGTH] = $request->getHeader('Content-Length') ?: '';
        return $requestInformation;
    }

    /**
     * Gets the information needed from the schema
     *
     * @param Schema $schema
     * @return array
     */
    private function gatherQueryInformation(Schema $schema) : array
    {
        $schemaConfig = $schema->getConfig();
        $mutationOperations = $schemaConfig->getMutation()->getFields();
        $queryOperations = $schemaConfig->getQuery()->getFields();
        $queryInformation[LoggerInterface::HAS_MUTATION] = count($mutationOperations) > 0 ? 'true' : 'false';
        $queryInformation[LoggerInterface::NUMBER_OF_OPERATIONS] =
            count($mutationOperations) + count($queryOperations);
        $operationNames = array_merge(array_keys($mutationOperations), array_keys($queryOperations));
        $queryInformation[LoggerInterface::OPERATION_NAMES] =
            count($operationNames) > 0 ? implode(",", $operationNames) : 'operationNameNotFound';
        return $queryInformation;
    }

    /**
     * Gets the information needed from the response
     *
     * @param HttpResponse $response
     * @return array
     */
    private function gatherResponseInformation(HttpResponse $response) : array
    {
        $responseInformation[LoggerInterface::X_MAGENTO_CACHE_ID] =
            $response->getHeader('X-Magento-Cache-Id')
                ? $response->getHeader('X-Magento-Cache_Id')->getFieldValue()
                : '';
        $responseInformation[LoggerInterface::HTTP_RESPONSE_CODE] = $response->getHttpResponseCode();
        return $responseInformation;
    }

    /**
     * Gets the field count for the whole request
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param string $query
     * @return int
     * @throws SyntaxError
     * @throws /Exception
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
