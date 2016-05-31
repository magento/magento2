<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Setup;

use Magento\Framework\Setup;

/**
 * Class PostInstallSampleData
 */
class InstallData implements Setup\InstallDataInterface
{
    /**
     * @var \Magento\Framework\Setup\SampleData\State
     */
    protected $state;

    /**
     * @param \Magento\Framework\Setup\SampleData\State $state
     */
    public function __construct(
        \Magento\Framework\Setup\SampleData\State $state
    ) {
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function install(Setup\ModuleDataSetupInterface $setup, Setup\ModuleContextInterface $moduleContext)
    {
        $this->state->clearState();
    }
}
