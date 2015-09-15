<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSampleData\Setup;

use Magento\Framework\Setup;

/**
 * Class PostInstallSampleData
 */
class InstallData implements Setup\InstallDataInterface
{
    /**
     * @var \Magento\SampleData\Helper\Deploy
     */
    protected $deploy;

    /**
     * @param \Magento\SampleData\Helper\Deploy $deploy
     */
    public function __construct(\Magento\SampleData\Helper\Deploy $deploy)
    {
        $this->deploy = $deploy;
    }

    /**
     * @inheritdoc
     */
    public function install(Setup\ModuleDataSetupInterface $setup, Setup\ModuleContextInterface $moduleContext)
    {
        $this->deploy->run();
    }
}
