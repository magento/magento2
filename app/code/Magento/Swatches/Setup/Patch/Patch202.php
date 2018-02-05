<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Setup\Patch;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\Store;
use Magento\Swatches\Model\Swatch;
use Zend_Db;
use Zend_Db_Expr;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch202
{


    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->updateAdminTextSwatchValues($setup);


        $setup->endSetup();

    }

    private function updateAdminTextSwatchValues(ModuleDataSetupInterface $setup
    )
    {
        $storeData = $setup->getConnection()
            ->select()
            ->from($setup->getTable('store'))
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
            $select = $setup->getConnection()
                ->select()
                ->joinLeft(
                    ["ls" => $setup->getTable('eav_attribute_option_swatch')],
                    new Zend_Db_Expr("ls.option_id = s.option_id AND ls.store_id = " . $storeData[Store::STORE_ID]),
                    ["value"]
                )
                ->where("s.store_id = ? ", Store::DEFAULT_STORE_ID)
                ->where("s.type = ? ", Swatch::SWATCH_TYPE_TEXTUAL)
                ->where("s.value = ?  or s.value is null", "");

            $setup->getConnection()->query(
                $setup->getConnection()->updateFromSelect(
                    $select,
                    ["s" => $setup->getTable('eav_attribute_option_swatch')]
                )
            );
        }

    }
}
