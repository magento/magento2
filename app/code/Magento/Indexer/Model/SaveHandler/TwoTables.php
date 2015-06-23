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
        $indexDocuments = [];
        foreach ($this->batch->getItems($documents) as $batchDocuments) {
            foreach ($batchDocuments as $documentName => $documentValue) {
                foreach ($documentValue as $fieldName => $fieldValue) {
                    $type = $this->getTypeByFieldName($fieldName);
                    $indexDocuments[$type][$documentName][$fieldName] = $fieldValue;
                }
            }
        }


        foreach ($this->dataTypes as $dataType) {
            $this->getAdapter()->insertMultiple($this->getTableName($dataType), $indexDocuments[$dataType]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(Dimension $dimension, \Traversable $documents)
    {
        foreach ($this->dataTypes as $dataType) {
            $this->getAdapter()->delete($this->getTableName($dataType), ['id' => array_column($documents, 'id')]);
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
     * @param string $fieldName
     * @return string
     */
    private function getTypeByFieldName($fieldName)
    {
        return $this->data['fields'][$fieldName]['type'];
    }
}
