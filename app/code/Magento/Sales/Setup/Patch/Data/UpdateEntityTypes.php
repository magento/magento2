<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateEntityTypes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * PatchInitial constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $salesSetupFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $salesSetup = $this->salesSetupFactory->create();
        $salesSetup->updateEntityTypes();
        $this->eavConfig->clear();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            InstallOrderStatusesAndInitialSalesConfig::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
