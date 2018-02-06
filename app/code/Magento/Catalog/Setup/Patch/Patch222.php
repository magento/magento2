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
class Patch222 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param UpgradeWebsiteAttributes $upgradeWebsiteAttributes
     */
    private $upgradeWebsiteAttributes;

    /**
     * @param UpgradeWebsiteAttributes $upgradeWebsiteAttributes
     */
    public function __construct(UpgradeWebsiteAttributes $upgradeWebsiteAttributes)
    {
        $this->upgradeWebsiteAttributes = $upgradeWebsiteAttributes;
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


        $this->upgradeWebsiteAttributes->upgrade($setup);


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
