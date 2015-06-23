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
use Magento\Indexer\Model\SaveHandler\Batch;

class TwoTables implements IndexerInterface
{
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
        $indexDocuments = [];
        foreach ($this->batch->getItems($documents) as $batchDocuments) {
            foreach ($batchDocuments as $documentName => $documentValue) {
                foreach ($documentValue as $fieldName => $fieldValue) {
                    $type = $this->getTypeByFieldName($fieldName);
                    $indexDocuments[$type][$documentName][$fieldName] = $fieldValue;
                }
            }
        }

        $tableName = $this->getTableName();
        $dataTypes = ['searchable', 'filterable'];
        foreach ($dataTypes as $dataType) {
            $this->getAdapter()->insertMultiple($tableName, $indexDocuments[$dataType]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(Dimension $dimension, \Traversable $documents)
    {
        foreach ($documents as $document) {
            $this->getAdapter()->delete($this->getTableName(), $document['id']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex(Dimension $dimension)
    {
        $tableName = $this->getTableName();
        $this->indexStructure->delete($tableName, [$dimension]);
        $this->indexStructure->create($tableName, $this->data, [$dimension]);
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @return string
     */
    private function getTableName()
    {
        return $this->getIndexName() . '_scope';
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
     * @param string $fieldName
     * @return string
     */
    private function getTypeByFieldName($fieldName)
    {
        return $this->data['fields'][$fieldName]['type'];
    }
}
