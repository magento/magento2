<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup\Patch;

use Magento\Eav\Model\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\OrderFactory;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch209
{


    /**
     * @param SalesSetupFactory $salesSetupFactory @param Config $eavConfig
     */
    public function __construct(SalesSetupFactory $salesSetupFactory,
                                Config $eavConfig)
    {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        //Correct wrong source model for "invoice" entity type, introduced by mistake in 2.0.1 upgrade.
        $salesSetup->updateEntityType(
            'invoice',
            'entity_model',
            \Magento\Sales\Model\ResourceModel\Order\Invoice::class
        );
        $this->eavConfig->clear();

    }

}
