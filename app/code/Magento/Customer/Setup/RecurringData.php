<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;

/**
 * Upgrade registered themes.
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * Init
     *
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($this->isNeedToDoReindex($setup)) {
            $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
            $indexer->reindexAll();
        }
    }

    /**
     * Check is re-index needed
     *
     * @param ModuleDataSetupInterface $setup
     * @return bool
     */
    private function isNeedToDoReindex(ModuleDataSetupInterface $setup) : bool
    {
        return !$setup->tableExists('customer_grid_flat')
            || $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID)
                ->getState()
                ->getStatus() == StateInterface::STATUS_INVALID;
    }
}
