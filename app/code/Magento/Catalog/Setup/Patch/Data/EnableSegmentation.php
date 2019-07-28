<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class EnableSegmentation.
 *
 * @package Magento\Catalog\Setup\Patch
 */
class EnableSegmentation implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * EnableSegmentation constructor.
     *
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
        $this->moduleDataSetup->startSetup();
        $setup = $this->moduleDataSetup;

        $catalogCategoryProductIndexColumns = array_keys(
            $setup->getConnection()->describeTable($setup->getTable('catalog_category_product_index'))
        );
        $storeSelect = $setup->getConnection()->select()->from($setup->getTable('store'))->where('store_id > 0');
        foreach ($setup->getConnection()->fetchAll($storeSelect) as $store) {
            $catalogCategoryProductIndexSelect = $setup->getConnection()->select()
                ->from(
                    $setup->getTable('catalog_category_product_index')
                )->where(
                    'store_id = ?',
                    $store['store_id']
                );
            $indexTable = $setup->getTable('catalog_category_product_index') .
                '_' .
                \Magento\Store\Model\Store::ENTITY .
                $store['store_id'];
            $setup->getConnection()->query(
                $setup->getConnection()->insertFromSelect(
                    $catalogCategoryProductIndexSelect,
                    $indexTable,
                    $catalogCategoryProductIndexColumns,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }
        $setup->getConnection()->delete($setup->getTable('catalog_category_product_index'));
        $setup->getConnection()->delete($setup->getTable('catalog_category_product_index_replica'));
        $setup->getConnection()->delete($setup->getTable('catalog_category_product_index_tmp'));

        $this->moduleDataSetup->endSetup();
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
}
