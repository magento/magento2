<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerSampleData\Setup;

use Magento\Framework\Setup;

class InstallData implements Setup\InstallDataInterface
{
    /**
     * @var Setup\SampleData\Executor
     */
    protected $executor;

    /**
     * @var Installer
     */
    protected $installer;

    public function __construct(Setup\SampleData\Executor $executor, Installer $installer)
    {
        $this->executor = $executor;
        $this->installer = $installer;
    }

    /**
     * {@inheritdoc}
     */
    public function install(Setup\ModuleDataSetupInterface $setup, Setup\ModuleContextInterface $moduleContext)
    {
        $this->executor->exec($this->installer);
    }
}
