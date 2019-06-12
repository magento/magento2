<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\SaveHandler;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

/**
 * Save handler for indexer.
 */
class IndexerHandler implements IndexerInterface
{
    /**
     * @var string[]
     */
    protected $dataTypes = ['searchable', 'filterable'];

    /**
     * @var IndexStructureInterface
     */
    protected $indexStructure;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var Resource|Resource
     */
    protected $resource;

    /**
     * @var Batch
     */
    protected $batch;

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @var IndexScopeResolverInterface[]
     */
    protected $scopeResolvers;

    /**
     * @param IndexStructureInterface $indexStructure
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @param IndexStructureInterface $indexStructure
     * @param ResourceConnection $resource
     * @param Batch $batch
     * @param \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
     * @param \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver $flatScopeResolver
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        ResourceConnection $resource,
        Batch $batch,
        IndexScopeResolver $indexScopeResolver,
        FlatScopeResolver $flatScopeResolver,
        array $data,
        $batchSize = 100
    ) {
        $this->indexStructure = $indexStructure;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->batch = $batch;
        $this->scopeResolvers[$this->dataTypes[0]] = $indexScopeResolver;
        $this->scopeResolvers[$this->dataTypes[1]] = $flatScopeResolver;
        $this->data = $data;
        $this->batchSize = $batchSize;

        $this->fields = [];
        $this->prepareFields();
    }

    /**
     * @inheritdoc
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->insertDocumentsForSearchable($batchDocuments, $dimensions);
            $this->insertDocumentsForFilterable($batchDocuments, $dimensions);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->dataTypes as $dataType) {
            foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
                $documentsId = array_column($batchDocuments, 'id');
                $this->connection->delete($this->getTableName($dataType, $dimensions), ['id' => $documentsId]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), $this->fields, $dimensions);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable($dimensions = [])
    {
        return true;
    }

    /**
     * Returns table name.
     *
     * @param string $dataType
     * @param Dimension[] $dimensions
     * @return string
     */
    protected function getTableName($dataType, $dimensions)
    {
        return $this->scopeResolvers[$dataType]->resolve($this->getIndexName(), $dimensions);
    }

    /**
     * Returns index name
     *
     * @return string
     */
    protected function getIndexName()
    {
        return $this->data['indexer_id'];
    }

    /**
     * Save searchable documents to storage.
     *
     * @param array $documents
     * @param Dimension[] $dimensions
     * @return void
     */
    private function insertDocumentsForSearchable(array $documents, array $dimensions)
    {
        $this->connection->insertOnDuplicate(
            $this->getTableName($this->dataTypes[0], $dimensions),
            $this->prepareSearchableFields($documents),
            ['data_index']
        );
    }

    /**
     * Save filterable documents to storage.
     *
     * @param array $documents
     * @param Dimension[] $dimensions
     * @return void
     */
    protected function insertDocumentsForFilterable(array $documents, array $dimensions)
    {
        $onDuplicate = [];
        foreach ($this->fields as $field) {
            if ($field['type'] === $this->dataTypes[1]) {
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
     * Prepare filterable fields.
     *
     * @param array $documents
     * @return array
     */
    protected function prepareFilterableFields(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            $documentFlat = ['entity_id' => $entityId];
            foreach ($this->fields as $field) {
                if ($field['type'] == $this->dataTypes[1]) {
                    $documentFlat[$field['name']] = $document[$field['name']];
                }
            }
            $insertDocuments[] = $documentFlat;
        }
        return $insertDocuments;
    }

    /**
     * Prepare searchable fields.
     *
     * @param array $documents
     * @return array
     */
    private function prepareSearchableFields(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            foreach ($this->fields as $field) {
                if ($field['type'] === $this->dataTypes[0]) {
                    $insertDocuments[] = [
                        'entity_id' => $entityId,
                        'attribute_id' => $field['name'],
                        'data_index' => $document[$field['name']],
                    ];
                }
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
            $this->fields = array_merge($this->fields, array_values($fieldset['fields']));
        }
    }
}
