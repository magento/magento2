<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup\Patch;

use Magento\Eav\Model\Config;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\OrderFactory;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch208 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param State $state
     */
    private $state;

    /**
     * @param State $state @param Config $eavConfig
     */
    public function __construct(State $state

        ,
                                Config $eavConfig)
    {
        $this->state = $state;
        $this->eavConfig = $eavConfig;
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
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $this->state->emulateAreaCode(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
            [$this, 'fillQuoteAddressIdInSalesOrderAddress'],
            [$setup]
        );
        $this->eavConfig->clear();

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
