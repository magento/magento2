<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Setup\Patch\Data;

use Magento\Store\Model\Store;
use Magento\Swatches\Model\Swatch;
use Zend_Db;
use Zend_Db_Expr;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdateAdminTextSwatchValues
 * @package Magento\Swatches\Setup\Patch
 */
class UpdateAdminTextSwatchValues implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * UpdateAdminTextSwatchValues constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->updateAdminTextSwatchValues();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddSwatchImageToDefaultAttribtueSet::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.2';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Update text swatch values for admin panel.
     */
    private function updateAdminTextSwatchValues()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $storeData = $connection
            ->select()
            ->from($this->moduleDataSetup->getTable('store'))
            ->where(Store::STORE_ID . "<> ? ", Store::DEFAULT_STORE_ID)
            ->order("sort_order desc")
            ->limit(1)
            ->query(Zend_Db::FETCH_ASSOC)
            ->fetch();

        if (is_array($storeData)) {

            /**
             * update eav_attribute_option_swatch as s
             * left join eav_attribute_option_swatch as ls on ls.option_id = s.option_id and ls.store_id = 1
             * set
             *
             * s.value = ls.value
             * where s.store_id = 0 and s.`type` = 0 and s.value = ""
             */

            /** @var \Magento\Framework\DB\Select $select */
            $select = $connection
                ->select()
                ->joinLeft(
                    ["ls" => $this->moduleDataSetup->getTable('eav_attribute_option_swatch')],
                    new Zend_Db_Expr("ls.option_id = s.option_id AND ls.store_id = " . $storeData[Store::STORE_ID]),
                    ["value"]
                )
                ->where("s.store_id = ? ", Store::DEFAULT_STORE_ID)
                ->where("s.type = ? ", Swatch::SWATCH_TYPE_TEXTUAL)
                ->where("s.value = ?  or s.value is null", "");

            $connection->query(
                $connection->updateFromSelect(
                    $select,
                    ["s" => $this->moduleDataSetup->getTable('eav_attribute_option_swatch')]
                )
            );
        }
    }
}
