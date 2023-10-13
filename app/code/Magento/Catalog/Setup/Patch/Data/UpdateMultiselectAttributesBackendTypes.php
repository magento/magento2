<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\NonTransactionableInterface;

class UpdateMultiselectAttributesBackendTypes implements DataPatchInterface, NonTransactionableInterface
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
     * @var array
     */
    private $triggersRestoreQueries = [];

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
        $this->triggersRestoreQueries = [];

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
        $attributesToMigrate = array_map('intval', $attributesToMigrate);

        $varcharTable = $setup->getTable('catalog_product_entity_varchar');
        $textTable = $setup->getTable('catalog_product_entity_text');

        $columns = $connection->describeTable($varcharTable);
        unset($columns['value_id']);
        $this->dropTriggers($textTable);
        $this->dropTriggers($varcharTable);
        try {
            $connection->query(
                $connection->insertFromSelect(
                    $connection->select()
                        ->from($varcharTable, array_keys($columns))
                        ->where('attribute_id in (?)', $attributesToMigrate, \Zend_Db::INT_TYPE),
                    $textTable,
                    array_keys($columns),
                    AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
            $connection->delete($varcharTable, ['attribute_id IN (?)' => $attributesToMigrate]);
        } finally {
            $this->restoreTriggers($textTable);
            $this->restoreTriggers($varcharTable);
        }

        foreach ($attributesToMigrate as $attributeId) {
            $eavSetup->updateAttribute($entityTypeId, $attributeId, 'backend_type', 'text');
        }

        $this->dataSetup->endSetup();

        return $this;
    }

    /**
     * Drop table triggers
     *
     * @param string $tableName
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    private function dropTriggers(string $tableName): void
    {
        $triggers = $this->dataSetup->getConnection()
            ->query('SHOW TRIGGERS LIKE \''. $tableName . '\'')
            ->fetchAll();

        if (!$triggers) {
            return;
        }

        foreach ($triggers as $trigger) {
            $triggerData = $this->dataSetup->getConnection()
                ->query('SHOW CREATE TRIGGER '. $trigger['Trigger'])
                ->fetch();
            $this->triggersRestoreQueries[$tableName][] =
                preg_replace('/DEFINER=[^\s]*/', '', $triggerData['SQL Original Statement']);
            // phpcs:ignore Magento2.SQL.RawQuery.FoundRawSql
            $this->dataSetup->getConnection()->query('DROP TRIGGER IF EXISTS ' . $trigger['Trigger']);
        }
    }

    /**
     * Restore table triggers.
     *
     * @param string $tableName
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    private function restoreTriggers(string $tableName): void
    {
        if (array_key_exists($tableName, $this->triggersRestoreQueries)) {
            foreach ($this->triggersRestoreQueries[$tableName] as $query) {
                $this->dataSetup->getConnection()->multiQuery($query);
            }
        }
    }
}
