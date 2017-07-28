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
 * @api
 * @since 2.0.0
 */
class IndexerHandler implements IndexerInterface
{
    /**
     * @var IndexStructureInterface
     * @since 2.0.0
     */
    private $indexStructure;

    /**
     * @var array
     * @since 2.0.0
     */
    private $data;

    /**
     * @var array
     * @since 2.0.0
     */
    private $fields;

    /**
     * @var Resource|Resource
     * @since 2.0.0
     */
    private $resource;

    /**
     * @var Batch
     * @since 2.0.0
     */
    private $batch;

    /**
     * @var Config
     * @since 2.0.0
     */
    private $eavConfig;

    /**
     * @var int
     * @since 2.0.0
     */
    private $batchSize;

    /**
     * @var IndexScopeResolverInterface
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        ResourceConnection $resource,
        Config $eavConfig,
        Batch $batch,
        IndexScopeResolverInterface $indexScopeResolver,
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
     * @since 2.0.0
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->insertDocuments($batchDocuments, $dimensions);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resource->getConnection()
                ->delete($this->getTableName($dimensions), ['entity_id in (?)' => $batchDocuments]);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), [], $dimensions);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @param Dimension[] $dimensions
     * @return string
     * @since 2.0.0
     */
    private function getTableName($dimensions)
    {
        return $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    private function getIndexName()
    {
        return $this->data['indexer_id'];
    }

    /**
     * @param array $documents
     * @param Dimension[] $dimensions
     * @return void
     * @since 2.0.0
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
     * @param array $documents
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function prepareFields()
    {
        foreach ($this->data['fieldsets'] as $fieldset) {
            $this->fields = array_merge($this->fields, $fieldset['fields']);
        }
    }
}
