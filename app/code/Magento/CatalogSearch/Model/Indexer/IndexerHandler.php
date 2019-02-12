<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;

/**
 * Catalog search indexer handler.
 *
 * @api
 * @since 100.0.2
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class IndexerHandler implements IndexerInterface
{
    /**
     * @var IndexStructureInterface
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
     * @param IndexStructureInterface $indexStructure
     * @param ResourceConnection $resource
     * @param Config $eavConfig
     * @param Batch $batch
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        ResourceConnection $resource,
        Config $eavConfig,
        Batch $batch,
        IndexScopeResolverInterface $indexScopeResolver,
        array $data,
        $batchSize = 500
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
     * @inheritdoc
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->insertDocuments($batchDocuments, $dimensions);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resource->getConnection()
                ->delete($this->getTableName($dimensions), ['entity_id in (?)' => $batchDocuments]);
        }
    }

    /**
     * @inheritdoc
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), [], $dimensions);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable($dimensions = [])
    {
        if (empty($dimensions)) {
            return true;
        }

        return $this->resource->getConnection()->isTableExists($this->getTableName($dimensions));
    }

    /**
     * Returns table name.
     *
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTableName($dimensions)
    {
        return $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);
    }

    /**
     * Returns index name.
     *
     * @return string
     */
    private function getIndexName()
    {
        return $this->data['indexer_id'];
    }

    /**
     * Add documents to storage.
     *
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
        $this->resource->getConnection()->insertOnDuplicate(
            $this->getTableName($dimensions),
            $documents,
            ['data_index']
        );
    }

    /**
     * Searchable filter preparation.
     *
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
     * Prepare fields.
     *
     * @return void
     */
    private function prepareFields()
    {
        foreach ($this->data['fieldsets'] as $fieldset) {
            $this->fields = array_merge($this->fields, $fieldset['fields']);
        }
    }
}
