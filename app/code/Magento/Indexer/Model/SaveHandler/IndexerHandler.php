<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\SaveHandler;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\IndexerInterface;
use Magento\Indexer\Model\IndexStructure;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Search\Model\ScopeResolver\FlatScopeResolver;
use Magento\Search\Model\ScopeResolver\IndexScopeResolver;

class IndexerHandler implements IndexerInterface
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
     * @var array
     */
    private $fields;

    /**
     * @var Resource|Resource
     */
    private $resource;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var IndexScopeResolverInterface[]
     */
    private $scopeResolvers;

    /**
     * @param IndexStructure $indexStructure
     * @param Resource $resource
     * @param Config $eavConfig
     * @param Batch $batch
     * @param IndexScopeResolver $indexScopeResolver
     * @param FlatScopeResolver $flatScopeResolver
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        IndexStructure $indexStructure,
        Resource $resource,
        Config $eavConfig,
        Batch $batch,
        IndexScopeResolver $indexScopeResolver,
        FlatScopeResolver $flatScopeResolver,
        array $data,
        $batchSize = 100
    ) {
        $this->indexStructure = $indexStructure;
        $this->resource = $resource;
        $this->batch = $batch;
        $this->eavConfig = $eavConfig;
        $this->scopeResolvers[$this->dataTypes[0]] = $indexScopeResolver;
        $this->scopeResolvers[$this->dataTypes[1]] = $flatScopeResolver;
        $this->data = $data;
        $this->batchSize = $batchSize;

        $this->fields = [];
        $this->prepareFields();
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            foreach ($this->dataTypes as $dataType) {
                $this->insertDocuments($dataType, $batchDocuments, $dimensions);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->dataTypes as $dataType) {
            foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
                $documentsId = array_column($batchDocuments, 'id');
                $this->getAdapter()->delete($this->getTableName($dataType, $dimensions), ['id' => $documentsId]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), $this->fields, $dimensions);
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
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTableName($dataType, $dimensions)
    {
        return $this->scopeResolvers[$dataType]->resolve($this->getIndexName(), $dimensions);
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
     * @param Dimension[] $dimensions
     * @return void
     */
    private function insertDocuments($dataType, array $documents, array $dimensions)
    {
        if ($dataType === $this->dataTypes[0]) {
            $documents = $this->prepareSearchableFields($documents);
        }
        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getTableName($dataType, $dimensions),
            $documents,
            ['data_index']
        );
    }

    /**
     * @param array $documents
     * @return array
     */
    private function prepareSearchableFields(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            foreach ($document as $attributeId => $fieldValue) {
                $insertDocuments[] = [
                    'entity_id' => $entityId,
                    'attribute_id' => $attributeId,
                    'data_index' => $fieldValue,
                ];
            }
        }

        return $insertDocuments;
    }

    /**
     * @return void
     */
    private function prepareFields()
    {
        foreach ($this->data['fieldsets'] as $fieldset) {
            $this->fields = array_merge($this->fields, $fieldset['fields']);
        }
    }
}
