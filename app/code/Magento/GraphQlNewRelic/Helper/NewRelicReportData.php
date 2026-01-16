<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlNewRelic\Helper;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ObjectType;
use Magento\Framework\GraphQl\Schema;

class NewRelicReportData
{
    private const PREFIX = '/GraphQl/Controller/GraphQl\\';
    private const BACKSLASH = '\\';
    private const MULTIPLE_QUERIES_FLAG = 'Multiple';

    /**
     * Get transaction data from GraphQl schema.
     *
     * @param Schema $schema
     * @param DocumentNode|string $querySource
     * @return array
     */
    public function getTransactionData(Schema $schema, DocumentNode|string $querySource): array
    {
        $gqlFieldsInfo = $this->getGqlFieldsInfo($schema);
        if (!$gqlFieldsInfo) {
            return [];
        }

        $gqlInfo = $this->extractGqlInfo($gqlFieldsInfo);
        $operationName = $this->getOperationNameFromQuery($querySource);

        $finalGqlCallName = $operationName !== ''
            ? $operationName
            : ($gqlInfo['field_count'] > 1 ? self::MULTIPLE_QUERIES_FLAG : $gqlInfo['first_field_name']);

        return [
            'transactionName' => $this->buildTransactionName($gqlInfo['gql_call_type'], $finalGqlCallName),
            'fieldCount' => $gqlInfo['field_count'],
            'fieldNames' => $gqlInfo['all_field_names'],
        ];
    }

    /**
     * @param ObjectType $gqlFieldsInfo
     * @return array
     */
    private function extractGqlInfo(ObjectType $gqlFieldsInfo): array
    {
        $gqlFields = $gqlFieldsInfo->getFields();

        return [
            'field_count' => count($gqlFields),
            'gql_call_type' => $gqlFieldsInfo->name,
            'first_field_name' => array_key_first($gqlFields) ?? '',
            'all_field_names' => array_keys($gqlFields),
        ];
    }

    /**
     * @param Schema|null $schema
     * @return ObjectType|null
     */
    private function getGqlFieldsInfo(?Schema $schema): ?ObjectType
    {
        if (!$schema) {
            return null;
        }

        $schemaConfig = $schema->getConfig();
        if (!$schemaConfig) {
            return null;
        }

        $mutation = $schemaConfig->getMutation();
        $hasMutationFields = $mutation && count($mutation->getFields()) > 0;

        return $hasMutationFields ? $mutation : $schemaConfig->getQuery();
    }

    /**
     * Build a transaction name based on query type and operation name.
     *
     * @param string $gqlCallType
     * @param string $operationName
     * @return string
     */
    private function buildTransactionName(string $gqlCallType, string $operationName): string
    {
        return self::PREFIX . $gqlCallType . self::BACKSLASH . $operationName;
    }

    /**
     * Get operation name from query.
     *
     * @param DocumentNode|string $querySource
     * @return string
     */
    public function getOperationNameFromQuery(DocumentNode|string $querySource): string
    {
        $query = $this->getQueryString($querySource);
        if ($query === '') {
            return '';
        }

        $bracePosition = stripos($query, '{');
        if ($bracePosition === false) {
            return '';
        }

        $operationBeginningSegment = substr($query, 0, $bracePosition);
        if ($operationBeginningSegment === '') {
            return '';
        }

        $operationName = '';
        if (preg_match('/(query|mutation)/', $operationBeginningSegment, $matches, PREG_OFFSET_CAPTURE)) {
            $strQueryOrMutation = $matches[0][0];
            $operationName = trim($this->getSubString($strQueryOrMutation, '(', $operationBeginningSegment));
        }

        return $operationName;
    }

    /**
     * @param DocumentNode|string $querySource
     * @return string
     */
    private function getQueryString(DocumentNode|string $querySource): string
    {
        if ($querySource instanceof DocumentNode) {
            return Printer::doPrint($querySource);
        }

        return $querySource;
    }

    /**
     * Get string in between two strings.
     *
     * @param string $startingStr
     * @param string $endingStr
     * @param string $str
     * @return string
     */
    private function getSubString(string $startingStr, string $endingStr, string $str): string
    {
        $subStrStart = strpos($str, $startingStr);
        $subStrStart += strlen($startingStr);

        $hasEndingStr = (strpos($str, $endingStr, $subStrStart)) !== false;
        $lengthOfSubstr = $hasEndingStr
            ? (strpos($str, $endingStr, $subStrStart) - $subStrStart)
            : (strlen($str) - $subStrStart);

        return substr($str, $subStrStart, $lengthOfSubstr);
    }
}
