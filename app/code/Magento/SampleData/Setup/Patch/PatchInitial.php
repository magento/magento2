<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Setup\Patch;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param \Magento\Framework\Setup\SampleData\State $state
     */
    private $state;

    /**
     * @param \Magento\Framework\Setup\SampleData\State $state
     */
    public function __construct(\Magento\Framework\Setup\SampleData\State $state)
    {
        $this->state = $state;
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
        $this->state->clearState();

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
