<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Framework\Indexer\IndexerRegistry;
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
    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
    }
}
