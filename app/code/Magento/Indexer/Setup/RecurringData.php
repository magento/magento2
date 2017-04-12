<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Framework\Indexer\ConfigInterface;

/**
 * Recurring data upgrade for indexer module
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * RecurringData constructor.
     *
     * @param IndexerFactory $indexerFactory
     * @param ConfigInterface $configInterface
     */
    public function __construct(
        IndexerFactory $indexerFactory,
        ConfigInterface $configInterface
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->configInterface = $configInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        foreach (array_keys($this->configInterface->getIndexers()) as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            if ($indexer->isScheduled()) {
                $indexer->getView()->unsubscribe()->subscribe();
            }
        }
    }
}
