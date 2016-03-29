<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Framework\App\ScopeResolverInterface;

class IndexStructure implements IndexStructureInterface
{
    /**
     * @var ElasticsearchAdapter
     */
    private $adapter;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @param ElasticsearchAdapter $adapter
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        ElasticsearchAdapter $adapter,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->adapter = $adapter;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        $indexerId,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->cleanIndex($storeId, $indexerId);
    }

    /**
     * {@inheritdoc}
     *
     */
    public function create(
        $indexerId,
        array $fields,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();;
        $this->adapter->checkIndex($storeId, $indexerId, false);
    }
}
