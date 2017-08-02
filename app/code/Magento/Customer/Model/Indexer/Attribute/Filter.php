<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Indexer\Attribute;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use Magento\Customer\Model\Customer;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;

/**
 * Class \Magento\Customer\Model\Indexer\Attribute\Filter
 *
 * @since 2.0.0
 */
class Filter
{
    /**
     * @var resource
     * @since 2.0.0
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver
     * @since 2.0.0
     */
    protected $flatScopeResolver;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     * @since 2.0.0
     */
    protected $indexerRegistry;

    /**
     * @param ResourceConnection $resource
     * @param FlatScopeResolver $flatScopeResolver
     * @param IndexerRegistry $indexerRegistry
     * @since 2.0.0
     */
    public function __construct(
        ResourceConnection $resource,
        FlatScopeResolver $flatScopeResolver,
        IndexerRegistry $indexerRegistry
    ) {
        $this->resource = $resource;
        $this->flatScopeResolver = $flatScopeResolver;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @param array $attributes
     * @return array
     * @since 2.0.0
     */
    public function filter(array $attributes)
    {
        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        if ($indexer->getState()->getStatus() != StateInterface::STATUS_VALID) {
            $tableName = $this->flatScopeResolver->resolve(Customer::CUSTOMER_GRID_INDEXER_ID, []);
            $columns = $this->resource->getConnection()->describeTable($tableName);

            foreach (array_keys($attributes) as $attributeCode) {
                if (!isset($columns[$attributeCode])) {
                    unset($attributes[$attributeCode]);
                }
            }
        }

        return $attributes;
    }
}
