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
     * @param IndexStructure $indexStructure
     * @param Resource $resource
     * @param array $data
     */
    public function __construct(IndexStructure $indexStructure, Resource $resource, array $data)
    {
        $this->indexStructure = $indexStructure;
        $this->data = $data;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex(Dimension $dimension, \Traversable $documents)
    {
        $fulltextIndexDocuments = [];
        $flatIndexDocuments = [];
        foreach ($documents as $document) {
            if ($document['type'] === 'searchable') {
                $fulltextIndexDocuments[] = $document;
            } elseif ($document['type'] === 'filterable') {
                $flatIndexDocuments[] = $document;
            }
        }

        foreach ($fulltextIndexDocuments as $document) {
            /** @var array $document */
            $this->getAdapter()->insertArray($this->getTableName(), array_keys($document), array_values($document));
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
        return $this->resource->getConnectionByName(Resource::DEFAULT_WRITE_RESOURCE);
    }
}
