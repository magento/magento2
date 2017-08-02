<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\SaveHandler;

use Magento\Framework\Search\Request\Dimension;

/**
 * Class \Magento\Framework\Indexer\SaveHandler\Grid
 *
 * @since 2.0.0
 */
class Grid extends IndexerHandler
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $dataTypes = ['searchable', 'filterable', 'virtual'];

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
