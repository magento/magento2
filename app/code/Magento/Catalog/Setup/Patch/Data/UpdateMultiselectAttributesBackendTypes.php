<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateMultiselectAttributesBackendTypes implements DataPatchInterface
{
    private const BATCH_SIZE = 10000;

    /**
     * @var ModuleDataSetupInterface
     */
    private $dataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Generator
     */
    private Generator $batchQueryGenerator;

    /**
     * @var int
     */
    private int $batchSize;

    /**
     * MigrateMultiselectAttributesData constructor.
     * @param ModuleDataSetupInterface $dataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Generator $batchQueryGenerator
     * @param int $batchSize
     */
    public function __construct(
        ModuleDataSetupInterface $dataSetup,
        EavSetupFactory $eavSetupFactory,
        Generator $batchQueryGenerator,
        int $batchSize = self::BATCH_SIZE
    ) {
        $this->dataSetup = $dataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->batchQueryGenerator = $batchQueryGenerator;
        $this->batchSize = $batchSize;
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

        $columns = $connection->describeTable($varcharTable);
        $primaryKey = 'value_id';
        unset($columns[$primaryKey]);
        $columnNames = array_keys($columns);
        $batchIterator = $this->batchQueryGenerator->generate(
            $primaryKey,
            $connection->select()
                ->from($varcharTable)
                ->where('attribute_id in (?)', $attributesToMigrate),
            $this->batchSize
        );
        foreach ($batchIterator as $select) {
            $selectForInsert = clone $select;
            $selectForInsert->reset(Select::COLUMNS);
            $selectForInsert->columns($columnNames);
            $connection->query(
                $connection->insertFromSelect($selectForInsert, $textTable, $columnNames)
            );
            $selectForDelete = clone $select;
            $selectForDelete->reset(Select::COLUMNS);
            $selectForDelete->columns($primaryKey);
            $selectForDelete = $connection->select()
                ->from($selectForDelete, $primaryKey);

            $connection->delete(
                $varcharTable,
                new Expression($primaryKey . ' IN ('  . $selectForDelete->assemble() . ')')
            );
        }

        foreach ($attributesToMigrate as $attributeId) {
            $eavSetup->updateAttribute($entityTypeId, $attributeId, 'backend_type', 'text');
        }

        $this->dataSetup->endSetup();

        return $this;
    }
}
