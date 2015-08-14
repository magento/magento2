<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Observer;

use Magento\Framework\App\Resource;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use Magento\Customer\Model\Customer;

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
     * @param Resource $resource
     * @param IndexerRegistry $indexerRegistry
     * @param FlatScopeResolver $flatScopeResolver
     */
    function __construct(
        Resource $resource,
        IndexerRegistry $indexerRegistry,
        FlatScopeResolver $flatScopeResolver
    ) {
        $this->resource = $resource;
        $this->indexerRegistry = $indexerRegistry;
        $this->flatScopeResolver = $flatScopeResolver;
    }

    public function syncCustomerGrid()
    {
        $indexer = $this->indexerRegistry->get(\Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID);
        $customerIds = $this->getCustomerIdsForReindex();
        if ($customerIds) {
            $indexer->reindexList($customerIds);
        }
    }

    protected function getCustomerIdsForReindex()
    {
        $connection = $this->resource->getConnection();
        $gridTableName = $this->flatScopeResolver->resolve(Customer::CUSTOMER_GRID_INDEXER_ID, []);

        $select = $connection->select()->from($connection->getTableName($gridTableName), 'last_visit_at')->order('last_visit_at DESC')->limit(1);
        $lastVisitAt = $connection->query($select)->fetchColumn();

        $select = $connection->select()
            ->from($connection->getTableName('customer_log'), 'customer_id')
            ->where('last_login_at > ?', $lastVisitAt);

        return $connection->query($select)->fetch() ?: [];
    }
}
