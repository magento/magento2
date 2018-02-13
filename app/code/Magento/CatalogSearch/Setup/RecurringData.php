<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Recurring data install.
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerInterfaceFactory;

    /**
     * Init
     *
     * @param IndexerInterfaceFactory $indexerInterfaceFactory
     */
    public function __construct(
        IndexerInterfaceFactory $indexerInterfaceFactory
    )
    {
        $this->indexerInterfaceFactory = $indexerInterfaceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->getIndexer('catalogsearch_fulltext')->reindexAll();
    }

    /**
     * Get indexer
     *
     * @param string $indexerId
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    private function getIndexer($indexerId)
    {
        return $this->indexerInterfaceFactory->create()->load($indexerId);
    }
}
