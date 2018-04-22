<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup;

use Magento\Framework\App\State;
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
     * @var State
     */
    private $state;

    /**
     * Init
     *
     * @param IndexerInterfaceFactory $indexerInterfaceFactory
     */
    public function __construct(
        IndexerInterfaceFactory $indexerInterfaceFactory,
        State $state
    ) {
        $this->indexerInterfaceFactory = $indexerInterfaceFactory;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_CRONTAB,
            [$this, 'reindex']
        );
    }

    /**
     * Run reindex.
     *
     * @return void
     */
    public function reindex()
    {
        $this->indexerInterfaceFactory->create()->load('catalogsearch_fulltext')->reindexAll();
    }
}
