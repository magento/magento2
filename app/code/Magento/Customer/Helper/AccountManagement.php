<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Customer\Model\Customer as CustomerModel;

/**
 * Customer helper for account management.
 */
class AccountManagement extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * AccountManagement constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        IndexerRegistry $indexerRegistry
    ) {
        parent::__construct($context);
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Check if customer is locked
     * @param string $lockExpires
     * @return bool
     */
    public function isCustomerLocked($lockExpires)
    {
        if ($lockExpires) {
            $lockExpires = new \DateTime($lockExpires);
            if ($lockExpires > new \DateTime()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reindex specified customer
     *
     * @param int $customerId
     * @return void
     */
    public function reindexCustomer($customerId)
    {
        $indexer = $this->indexerRegistry->get(CustomerModel::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexList([$customerId]);
    }
}
