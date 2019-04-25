<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\OfflineShipping\Model\Carrier\Tablerate;

/**
 * Update for shipping_tablerate table for using price with discount in condition.
 */
class UpdateShippingTablerate implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        $connection->update(
            $this->moduleDataSetup->getTable('shipping_tablerate'),
            ['condition_name' => 'package_value_with_discount'],
            [new \Zend_Db_Expr('condition_name = \'package_value\'')]
        );
        $connection->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['value' => 'package_value_with_discount'],
            [
                new \Zend_Db_Expr('value = \'package_value\''),
                new \Zend_Db_Expr('path = \'carriers/tablerate/condition_name\'')
            ]
        );
        $this->moduleDataSetup->getConnection()->endSetup();

        $connection->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
