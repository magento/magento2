<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\SaveHandler;

use Magento\Framework\Search\Request\Dimension;

class Grid extends IndexerHandler
{
    /**
     * @var string[]
     */
    protected $dataTypes = ['searchable', 'filterable', 'virtual'];

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->insertDocumentsForFilterable($batchDocuments, $dimensions);
        }
    }

    /**
     * @param array $documents
     * @param Dimension[] $dimensions
     * @return void
     */
    protected function insertDocumentsForFilterable(array $documents, array $dimensions)
    {
        $onDuplicate = [];
        foreach ($this->fields as $field) {
            if (in_array($field['type'], $this->dataTypes)) {
                $onDuplicate[] = $field['name'];
            }
        }

        $this->connection->insertOnDuplicate(
            $this->getTableName($this->dataTypes[1], $dimensions),
            $this->prepareFilterableFields($documents),
            $onDuplicate
        );
    }

    /**
     * @param array $documents
     * @return array
     */
    protected function prepareFilterableFields(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            $documentFlat = ['entity_id' => $entityId];
            foreach ($this->fields as $field) {
                if (in_array($field['type'], $this->dataTypes)) {
                    $documentFlat[$field['name']] = $document[$field['name']];
                }
            }
            $insertDocuments[] = $documentFlat;
        }
        return $insertDocuments;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $ids)
    {
        foreach ($this->batch->getItems($ids, $this->batchSize) as $batchIds) {
            $this->connection->delete(
                $this->getTableName('filterable', $dimensions),
                ['entity_id IN(?)' => $batchIds]
            );
        }
    }
}
