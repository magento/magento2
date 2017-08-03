<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Setup;

use Magento\Framework\Setup;

/**
 * Class PostInstallSampleData
 * @since 2.0.0
 */
class InstallData implements Setup\InstallDataInterface
{
    /**
     * @var \Magento\Framework\Setup\SampleData\State
     * @since 2.0.0
     */
    protected $state;

    /**
     * @param \Magento\Framework\Setup\SampleData\State $state
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Setup\SampleData\State $state
    ) {
        $this->state = $state;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function install(Setup\ModuleDataSetupInterface $setup, Setup\ModuleContextInterface $moduleContext)
    {
        $this->state->clearState();
    }
}
