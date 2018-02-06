<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch221 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param UpgradeWidgetData $upgradeWidgetData
     */
    private $upgradeWidgetData;

    /**
     * @param UpgradeWidgetData $upgradeWidgetData
     */
    public function __construct(UpgradeWidgetData $upgradeWidgetData)
    {
        $this->upgradeWidgetData = $upgradeWidgetData;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();


        $this->upgradeWidgetData->upgrade();


        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


}
