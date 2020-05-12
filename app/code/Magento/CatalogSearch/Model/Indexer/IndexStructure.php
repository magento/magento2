<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Catalog search index structure.
 *
 * @api
 * @since 100.0.2
 */
class IndexStructure implements IndexStructureInterface
{
    /**
     * @var IndexStructureInterface
     */
    private $indexStructureEntity;

    /**
     * @var IndexStructureFactory
     */
    private $indexStructureFactory;

    /**
     * @var Resource
     * @deprecated
     * @see \Magento\Elasticsearch
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     * @deprecated
     * @see \Magento\Elasticsearch
     */
    private $indexScopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param IndexStructureFactory|null $indexStructureFactory
     */
    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolverInterface $indexScopeResolver,
        IndexStructureFactory $indexStructureFactory = null
    ) {
        $this->resource = $resource;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->indexStructureFactory = $indexStructureFactory ? : ObjectManager::getInstance()
            ->get(IndexStructureFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function delete($index, array $dimensions = [])
    {
        return $this->getEntity()->delete($index, $dimensions);
    }

    /**
     * @inheritdoc
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        return $this->getEntity()->create($index, $fields, $dimensions);
    }

    /**
     * Get instance of current index structure
     *
     * @return IndexStructureInterface
     */
    private function getEntity()
    {
        if (empty($this->indexStructureEntity)) {
            $this->indexStructureEntity = $this->indexStructureFactory->create();
        }
        return $this->indexStructureEntity;
    }
}
