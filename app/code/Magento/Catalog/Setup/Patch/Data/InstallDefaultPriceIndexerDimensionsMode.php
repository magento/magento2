<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;

/**
 * Class InstallDefaultCategories data patch.
 *
 * @package Magento\Catalog\Setup\Patch
 */
class InstallDefaultPriceIndexerDimensionsMode implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $configTable = $this->moduleDataSetup->getTable('core_config_data');

        $this->moduleDataSetup->getConnection()->insert(
            $configTable,
            [
                'scope' => 'default',
                'scope_id' => 0,
                'value' => DimensionModeConfiguration::DIMENSION_NONE,
                'path' => ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.2.6';
    }
}
