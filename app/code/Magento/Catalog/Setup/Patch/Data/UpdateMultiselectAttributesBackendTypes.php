<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateMultiselectAttributesBackendTypes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $dataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * MigrateMultiselectAttributesData constructor.
     * @param ModuleDataSetupInterface $dataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $dataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->dataSetup = $dataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
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

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->dataSetup->startSetup();
        $setup = $this->dataSetup;
        $connection = $setup->getConnection();

        $attributeTable = $setup->getTable('eav_attribute');
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->dataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributesToMigrate = $connection->fetchCol(
            $connection
                ->select()
                ->from($attributeTable, ['attribute_id'])
                ->where('entity_type_id = ?', $entityTypeId)
                ->where('backend_type = ?', 'varchar')
                ->where('frontend_input = ?', 'multiselect')
        );

        $varcharTable = $setup->getTable('catalog_product_entity_varchar');
        $textTable = $setup->getTable('catalog_product_entity_text');
        $varcharTableDataSql = $connection
            ->select()
            ->from($varcharTable)
            ->where('attribute_id in (?)', $attributesToMigrate);
        $dataToMigrate = array_map(static function ($row) {
            $row['value_id'] = null;
            return $row;
        }, $connection->fetchAll($varcharTableDataSql));

        foreach (array_chunk($dataToMigrate, 2000) as $dataChunk) {
            $connection->insertMultiple($textTable, $dataChunk);
        }

        $connection->query($connection->deleteFromSelect($varcharTableDataSql, $varcharTable));

        foreach ($attributesToMigrate as $attributeId) {
            $eavSetup->updateAttribute($entityTypeId, $attributeId, 'backend_type', 'text');
        }

        $this->dataSetup->endSetup();

        return $this;
    }
}
