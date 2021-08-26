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
use Magento\Framework\GraphQl\Schema;
use Magento\GraphQl\Model\Query\Logger\LoggerInterface;

/**
 * Helper class to collect data for logging GraphQl queries
 */
class LogData
{
    /**
     * Extracts relevant information about the query
     *
     * @param RequestInterface $request
     * @param array $data
     * @param Schema $schema
     * @return array
     *
     * @throws SyntaxError
     */
    public function getQueryInformation(RequestInterface $request, array $data, Schema $schema) : array
    {
        $query = $data['query'] ?? '';
        $queryInformation = [];
        $queryInformation[LoggerInterface::HTTP_METHOD] = $request->getMethod();
        $queryInformation[LoggerInterface::STORE_HEADER] = $request->getHeader('Store') ?: '';
        $queryInformation[LoggerInterface::CURRENCY_HEADER] = $request->getHeader('Currency') ?: '';
        $queryInformation[LoggerInterface::HAS_AUTH_HEADER] = $request->getHeader('Authorization') ? 'true' : 'false';
        $queryInformation[LoggerInterface::IS_CACHEABLE] = $request->getHeader('X-Magento-Cache-Id') ? 'true' : 'false';
        $queryInformation[LoggerInterface::QUERY_LENGTH] = $request->getHeader('Content-Length') ?: '';

        $schemaConfig = $schema->getConfig();
        $configMutationFields = $schemaConfig->getMutation()->getFields();
        $configQueryFields = $schemaConfig->getQuery()->getFields();
        $queryInformation[LoggerInterface::HAS_MUTATION] = count($configMutationFields) > 0 ? 'true' : 'false';
        $queryInformation[LoggerInterface::NUMBER_OF_QUERIES] =
            count($configMutationFields) + count($configQueryFields);

        $queryNames = array_merge(array_keys($configMutationFields), array_keys($configQueryFields));
        $queryInformation[LoggerInterface::QUERY_NAMES] =
            count($queryNames) > 0 ? implode(", ", $queryNames) : 'operationNameNotFound';
        $queryInformation[LoggerInterface::QUERY_COMPLEXITY] = $this->getFieldCount($query);

        return $queryInformation;
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
