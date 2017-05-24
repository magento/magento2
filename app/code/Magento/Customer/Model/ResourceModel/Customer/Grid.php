<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Customer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use Magento\Customer\Model\Customer;

/**
 * @deprecated
 */
class Grid
{
    /** @var Resource */
    protected $resource;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /** @var FlatScopeResolver  */
    protected $flatScopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexerRegistry $indexerRegistry
     * @param FlatScopeResolver $flatScopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        IndexerRegistry $indexerRegistry,
        FlatScopeResolver $flatScopeResolver
    ) {
        $this->resource = $resource;
        $this->indexerRegistry = $indexerRegistry;
        $this->flatScopeResolver = $flatScopeResolver;
    }

    /**
     * Synchronize customer grid
     *
     * @return void
     *
     * @deprecated
     */
    public function syncCustomerGrid()
    {
        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $customerIds = $this->getCustomerIdsForReindex();
        if ($customerIds) {
            $indexer->reindexList($customerIds);
        }
    }

    /**
     * Retrieve customer IDs for reindex
     *
     * @return array
     *
     * @deprecated
     */
    protected function getCustomerIdsForReindex()
    {
        $connection = $this->resource->getConnection();
        $gridTableName = $this->flatScopeResolver->resolve(Customer::CUSTOMER_GRID_INDEXER_ID, []);

        $select = $connection->select()
            ->from($this->resource->getTableName($gridTableName), 'last_visit_at')
            ->order('last_visit_at DESC')
            ->limit(1);
        $lastVisitAt = $connection->query($select)->fetchColumn();

        $select = $connection->select()
            ->from($this->resource->getTableName('customer_log'), 'customer_id')
            ->where('last_login_at > ?', $lastVisitAt);

        $customerIds = [];
        foreach ($connection->query($select)->fetchAll() as $row) {
            $customerIds[] = $row['customer_id'];
        }

        return $customerIds;
    }
}
