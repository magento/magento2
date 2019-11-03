<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Theme\Model\Theme\Registration;
use Magento\Theme\Model\Data\Design\Config;

/**
 * Upgrade registered themes
 */
class RecurringData implements InstallDataInterface
{
    /**
     * Theme registration
     *
     * @var Registration
     */
    private $themeRegistration;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * Init
     *
     * @param Registration $themeRegistration
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(Registration $themeRegistration, IndexerRegistry $indexerRegistry)
    {
        $this->themeRegistration = $themeRegistration;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $indexer = $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $this->themeRegistration->register();
    }
}
