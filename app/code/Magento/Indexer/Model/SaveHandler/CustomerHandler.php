<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\SaveHandler;

class CustomerHandler extends IndexerHandler
{
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->insertDocumentsForFilterable($batchDocuments, $dimensions);
        }
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
}
