<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Indexer\Model\SaveHandler\Batch;
use Magento\Indexer\Model\ScopeResolver\IndexScopeResolver;

class IndexerHandler implements IndexerInterface
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
     * @var IndexScopeResolverInterface
     */
    private $indexScopeResolver;

    /**
     * @param IndexStructure $indexStructure
     * @param Resource|Resource $resource
     * @param Config $eavConfig
     * @param Batch $batch
     * @param \Magento\Indexer\Model\ScopeResolver\IndexScopeResolver $indexScopeResolver
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        IndexStructure $indexStructure,
        Resource $resource,
        Config $eavConfig,
        Batch $batch,
        IndexScopeResolver $indexScopeResolver,
        array $data,
        $batchSize = 100
    ) {
        $this->indexScopeResolver = $indexScopeResolver;
        $this->indexStructure = $indexStructure;
        $this->resource = $resource;
        $this->batch = $batch;
        $this->eavConfig = $eavConfig;
        $this->data = $data;
        $this->fields = [];

        $this->prepareFields();
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->insertDocuments($batchDocuments, $dimensions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->getAdapter()->delete($this->getTableName($dimensions), ['entity_id in (?)' => $batchDocuments]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTableName($dimensions)
    {
        return $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);
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
     * @param array $documents
     * @param Dimension[] $dimensions
     * @return void
     */
    private function insertDocuments(array $documents, array $dimensions)
    {
        $documents = $this->prepareSearchableFields($documents);
        if (empty($documents)) {
            return;
        }
        $this->getAdapter()->insertOnDuplicate(
            $this->getTableName($dimensions),
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
                $insertDocuments[$entityId . '_' . $attributeId] = [
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
