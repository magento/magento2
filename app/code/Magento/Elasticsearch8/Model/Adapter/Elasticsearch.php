<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch8\Model\Adapter;

/**
 * Elasticsearch adapter
 * @deprecated Elasticsearch8 is no longer supported by Adobe
 * @see this class will be responsible for ES8 only
 */
class Elasticsearch extends \Magento\Elasticsearch\Model\Adapter\Elasticsearch
{
    /**
     * Reformat documents array to bulk format
     *
     * @param array $documents
     * @param string $indexName
     * @param string $action
     * @return array
     */
    public function getDocsArrayInBulkIndexFormat(
        $documents,
        $indexName,
        $action = self::BULK_ACTION_INDEX
    ): array {
        $bulkArray = [
            'index' => $indexName,
            'body' => [],
            'refresh' => true,
        ];

        foreach ($documents as $id => $document) {
            $bulkArray['body'][] = [
                $action => [
                    '_id' => $id,
                    '_index' => $indexName
                ]
            ];

            if ($action == self::BULK_ACTION_INDEX) {
                $bulkArray['body'][] = $document;
            }
        }

        return $bulkArray;
    }
}
