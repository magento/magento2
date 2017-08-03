<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Framework\App\ScopeResolverInterface;

/**
 * Class \Magento\Elasticsearch\Model\Indexer\IndexStructure
 *
 * @since 2.1.0
 */
class IndexStructure implements IndexStructureInterface
{
    /**
     * @var ElasticsearchAdapter
     * @since 2.1.0
     */
    private $adapter;

    /**
     * @var ScopeResolverInterface
     * @since 2.1.0
     */
    private $scopeResolver;

    /**
     * @param ElasticsearchAdapter $adapter
     * @param ScopeResolverInterface $scopeResolver
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function delete(
        $indexerId,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->cleanIndex($scopeId, $indexerId);
    }

    /**
     * {@inheritdoc}
     *
     * @since 2.1.0
     */
    public function create(
        $indexerId,
        array $fields,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->checkIndex($scopeId, $indexerId, false);
    }
}
