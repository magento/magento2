<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\SaveHandler;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Indexer\Model\IndexStructure;

class TwoTables implements IndexerInterface
{
    /**
     * @var string[]
     */
    private $dataTypes = ['searchable', 'filterable'];

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Resource|Resource
     */
    private $resource;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @param IndexStructure $indexStructure
     * @param Resource|Resource $resource
     * @param Batch $batch
     * @param array $data
     */
    public function __construct(IndexStructure $indexStructure, Resource $resource, Batch $batch, array $data)
    {
        $this->indexStructure = $indexStructure;
        $this->data = $data;
        $this->resource = $resource;
        $this->batch = $batch;
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex(Dimension $dimension, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents) as $batchDocuments) {
            $indexDocuments = [];
            foreach ($batchDocuments as $documentName => $documentValue) {
                foreach ($this->data['fields'] as $fieldName => $fieldValue) {
                    if (isset ($documentValue[$fieldName])) {
                        $indexDocuments[$fieldValue['type']][$documentName][$fieldName] = $documentValue[$fieldName];
                    }
                }
            }
            foreach ($this->dataTypes as $dataType) {
                $this->insertDocuments($dataType, $indexDocuments);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(Dimension $dimension, \Traversable $documents)
    {
        foreach ($this->dataTypes as $dataType) {
            foreach ($this->batch->getItems($documents) as $batchDocuments) {
                $documentsId = array_column($batchDocuments, 'id');
                $this->getAdapter()->delete($this->getTableName($dataType), ['id' => $documentsId]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex(Dimension $dimension)
    {
        foreach ($this->dataTypes as $dataType) {
            $tableName = $this->getTableName($dataType);
            $this->indexStructure->delete($tableName, [$dimension]);
            $this->indexStructure->create($tableName, $this->data, [$dimension]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @param string $dataType
     * @return string
     */
    private function getTableName($dataType)
    {
        return $this->getIndexName() . $dataType . '_scope';
    }

    /**
     * @return string
     */
    private function getIndexName()
    {
        return $this->data['indexer_id'];
    }

    /**
     * @return AdapterInterface
     */
    private function getAdapter()
    {
        return $this->resource->getConnection(Resource::DEFAULT_WRITE_RESOURCE);
    }

    /**
     * @param string $dataType
     * @param array $documents
     * @return void
     */
    private function insertDocuments($dataType, array $documents)
    {
        if ($dataType === 'searchable') {
            $documents = $this->insertSearchable($documents);
        }
        $this->getAdapter()->insertMultiple($this->getTableName($dataType), $documents[$dataType]);
    }

    /**
     * @param array $documents
     * @return array
     */
    private function insertSearchable(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $document) {
            $entityId = $document['id'];
            unset($document['id']);
            foreach ($document as $fieldName => $fieldValue) {
                $attributeId = $fieldName;
                $insertDocuments[] = [
                    'entity_id' => $entityId,
                    'attribute_id' => $attributeId,
                    'data_index' => $fieldValue,
                ];
            }
        }

        return $insertDocuments;
    }
}
