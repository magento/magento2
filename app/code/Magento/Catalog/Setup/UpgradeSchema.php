<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup;

use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->addSupportVideoMediaAttributes($setup);
            $this->removeGroupPrice($setup);
        }
        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $this->addUniqueKeyToCategoryProductTable($setup);
        }
        if (version_compare($context->getVersion(), '2.1.4', '<')) {
            $this->addSourceEntityIdToProductEavIndex($setup);
        }
        if (version_compare($context->getVersion(), '2.1.5', '<')) {
            $this->addPercentageValueColumn($setup);
            $tables = [
                'catalog_product_index_price_cfg_opt_agr_idx',
                'catalog_product_index_price_cfg_opt_agr_tmp',
                'catalog_product_index_price_cfg_opt_idx',
                'catalog_product_index_price_cfg_opt_tmp',
                'catalog_product_index_price_final_idx',
                'catalog_product_index_price_final_tmp',
                'catalog_product_index_price_idx',
                'catalog_product_index_price_opt_agr_idx',
                'catalog_product_index_price_opt_agr_tmp',
                'catalog_product_index_price_opt_idx',
                'catalog_product_index_price_opt_tmp',
                'catalog_product_index_price_tmp',
            ];
            foreach ($tables as $table) {
                $setup->getConnection()->modifyColumn(
                    $setup->getTable($table),
                    'customer_group_id',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'nullable' => false,
                        'unsigned' => true,
                        'default' => '0',
                        'comment' => 'Customer Group ID',
                    ]
                );
            }
            $this->recreateCatalogCategoryProductIndexTmpTable($setup);
        }
        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            //remove fk from price index table
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('catalog_product_index_price'),
                $setup->getFkName(
                    'catalog_product_index_price',
                    'entity_id',
                    'catalog_product_entity',
                    'entity_id'
                )
            );
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('catalog_product_index_price'),
                $setup->getFkName(
                    'catalog_product_index_price',
                    'website_id',
                    'store_website',
                    'website_id'
                )
            );
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('catalog_product_index_price'),
                $setup->getFkName(
                    'catalog_product_index_price',
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                )
            );

            $this->addReplicaTable($setup, 'catalog_product_index_eav', 'catalog_product_index_eav_replica');
            $this->addReplicaTable(
                $setup,
                'catalog_product_index_eav_decimal',
                'catalog_product_index_eav_decimal_replica'
            );
            $this->addPathKeyToCategoryEntityTableIfNotExists($setup);
            //  By adding 'catalog_product_index_price_replica' we provide separation of tables
            //  used for indexation write and read operations and affected models.
            $this->addReplicaTable(
                $setup,
                'catalog_product_index_price',
                'catalog_product_index_price_replica'
            );
            // the same for 'catalog_category_product_index'
            $this->addReplicaTable(
                $setup,
                'catalog_category_product_index',
                'catalog_category_product_index_replica'
            );
        }

        if (version_compare($context->getVersion(), '2.2.3', '<')) {
            $this->addCatalogProductFrontendActionTable($setup);
        }

        if (version_compare($context->getVersion(), '2.2.2', '<')) {
            $this->fixCustomerGroupIdColumn($setup);
        }

        $setup->endSetup();
    }

    /**
     * Change definition of customer group id column
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function fixCustomerGroupIdColumn(SchemaSetupInterface $setup)
    {
        $tables = [
            'catalog_product_entity_tier_price',
            'catalog_product_index_price_cfg_opt_agr_idx',
            'catalog_product_index_price_cfg_opt_agr_tmp',
            'catalog_product_index_price_cfg_opt_idx',
            'catalog_product_index_price_cfg_opt_tmp',
            'catalog_product_index_price_final_idx',
            'catalog_product_index_price_final_tmp',
            'catalog_product_index_price_idx',
            'catalog_product_index_price_opt_agr_idx',
            'catalog_product_index_price_opt_agr_tmp',
            'catalog_product_index_price_opt_idx',
            'catalog_product_index_price_opt_tmp',
            'catalog_product_index_price_tmp',
        ];
        foreach ($tables as $table) {
            $setup->getConnection()->modifyColumn(
                $setup->getTable($table),
                'customer_group_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => '0',
                    'comment' => 'Customer Group ID',
                ]
            );
        }
    }

    /**
     * Add table which allows to hold product frontend actions like product view or comparison
     * with next definition: visitor or customer definition, product definition and added time in JS format
     *
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function addCatalogProductFrontendActionTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_frontend_action'))
            ->addColumn(
                'action_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product Action Id'
            )
            ->addColumn(
                'type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Type of product action'
            )
            ->addColumn(
                'visitor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Visitor Id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'added_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['nullable' => false],
                'Added At'
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalog_product_frontend_action',
                    ['visitor_id', 'product_id', 'type_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['visitor_id', 'product_id', 'type_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalog_product_frontend_action',
                    ['customer_id', 'product_id', 'type_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['customer_id', 'product_id', 'type_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName('catalog_product_frontend_action', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            //should be uncommented when this issue become fixed @MAGETWO-69393
//            ->addForeignKey(
//                $installer->getFkName(
//                    'catalog_product_frontend_action',
//                    'product_id',
//                    'catalog_product_entity',
//                    'entity_id'
//                ),
//                'product_id',
//                $installer->getTable('catalog_product_entity'),
//                'entity_id',
//                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
//            )
            ->setComment('Catalog Product Frontend Action Table');
        $installer->getConnection()->createTable($table);
    }

    /**
     * Add the column 'source_id' to the Product EAV index tables.
     * It allows to identify which entity was used to create value in the index.
     * It is useful to identify original entity in a composite products.
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addSourceEntityIdToProductEavIndex(SchemaSetupInterface $setup)
    {
        $tables = [
            'catalog_product_index_eav',
            'catalog_product_index_eav_idx',
            'catalog_product_index_eav_tmp',
            'catalog_product_index_eav_decimal',
            'catalog_product_index_eav_decimal_idx',
            'catalog_product_index_eav_decimal_tmp',
        ];
        $connection = $setup->getConnection();
        foreach ($tables as $tableName) {
            $tableName = $setup->getTable($tableName);
            if (!$connection->tableColumnExists($tableName, 'source_id')) {
                $connection->addColumn(
                    $tableName,
                    'source_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Original entity Id for attribute value',
                    ]
                );
                $connection->dropIndex($tableName, $connection->getPrimaryKeyName($tableName));
                $primaryKeyFields = ['entity_id', 'attribute_id', 'store_id', 'value', 'source_id'];
                $setup->getConnection()->addIndex(
                    $tableName,
                    $connection->getIndexName($tableName, $primaryKeyFields),
                    $primaryKeyFields,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY
                );
            }
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addUniqueKeyToCategoryProductTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addIndex(
            $setup->getTable('catalog_category_product'),
            $setup->getIdxName(
                'catalog_category_product',
                ['category_id', 'product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['category_id', 'product_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function createValueToEntityTable(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'catalog_product_entity_media_gallery_value_to_entity'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable(Gallery::GALLERY_VALUE_TO_ENTITY_TABLE))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Value media Entry ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product entity ID'
            )
            ->addIndex(
                $setup->getIdxName(
                    Gallery::GALLERY_VALUE_TO_ENTITY_TABLE,
                    ['value_id', 'entity_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['value_id', 'entity_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName(
                    Gallery::GALLERY_VALUE_TO_ENTITY_TABLE,
                    'value_id',
                    Gallery::GALLERY_TABLE,
                    'value_id'
                ),
                'value_id',
                $setup->getTable(Gallery::GALLERY_TABLE),
                'value_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    Gallery::GALLERY_VALUE_TO_ENTITY_TABLE,
                    'entity_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'entity_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Link Media value to Product entity table');
        $setup->getConnection()->createTable($table);
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addForeignKeys(SchemaSetupInterface $setup)
    {
        /**
         * Add foreign keys again
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                Gallery::GALLERY_VALUE_TABLE,
                'value_id',
                Gallery::GALLERY_TABLE,
                'value_id'
            ),
            $setup->getTable(Gallery::GALLERY_VALUE_TABLE),
            'value_id',
            $setup->getTable(Gallery::GALLERY_TABLE),
            'value_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                Gallery::GALLERY_VALUE_TABLE,
                'store_id',
                $setup->getTable('store'),
                'store_id'
            ),
            $setup->getTable(Gallery::GALLERY_VALUE_TABLE),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addSupportVideoMediaAttributes(SchemaSetupInterface $setup)
    {
        if ($setup->tableExists(Gallery::GALLERY_VALUE_TO_ENTITY_TABLE)) {
            return;
        }

        /** Add support video media attribute */
        $this->createValueToEntityTable($setup);
        /**
         * Add media type property to the Gallery entry table
         */
        $setup->getConnection()->addColumn(
            $setup->getTable(Gallery::GALLERY_TABLE),
            'media_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => false,
                'default' => ImageEntryConverter::MEDIA_TYPE_CODE,
                'comment' => 'Media entry type'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable(Gallery::GALLERY_TABLE),
            'disabled',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Visibility status'
            ]
        );

        /**
         * Drop entity Id columns
         */
        $setup->getConnection()->dropColumn($setup->getTable(Gallery::GALLERY_TABLE), 'entity_id');

        /**
         * Drop primary index
         */
        $setup->getConnection()->dropForeignKey(
            $setup->getTable(Gallery::GALLERY_VALUE_TABLE),
            $setup->getFkName(
                Gallery::GALLERY_VALUE_TABLE,
                'value_id',
                Gallery::GALLERY_TABLE,
                'value_id'
            )
        );
        $setup->getConnection()->dropForeignKey(
            $setup->getTable(Gallery::GALLERY_VALUE_TABLE),
            $setup->getFkName(
                Gallery::GALLERY_VALUE_TABLE,
                'store_id',
                'store',
                'store_id'
            )
        );
        $setup->getConnection()->dropIndex($setup->getTable(Gallery::GALLERY_VALUE_TABLE), 'primary');
        $setup->getConnection()->addColumn(
            $setup->getTable(Gallery::GALLERY_VALUE_TABLE),
            'record_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'primary' => true,
                'auto_increment' => true,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Record Id'
            ]
        );

        /**
         * Add index 'value_id'
         */
        $setup->getConnection()->addIndex(
            $setup->getTable(Gallery::GALLERY_VALUE_TABLE),
            $setup->getConnection()->getIndexName(
                $setup->getTable(Gallery::GALLERY_VALUE_TABLE),
                'value_id',
                'index'
            ),
            'value_id'
        );
        $this->addForeignKeys($setup);
    }

    /**
     * Remove Group Price
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function removeGroupPrice(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $fields = [
            ['table' => 'catalog_product_index_price_final_idx', 'column' => 'base_group_price'],
            ['table' => 'catalog_product_index_price_final_tmp', 'column' => 'base_group_price'],
            ['table' => 'catalog_product_index_price', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_cfg_opt_agr_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_cfg_opt_agr_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_cfg_opt_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_cfg_opt_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_final_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_final_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_opt_agr_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_opt_agr_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_opt_idx', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_opt_tmp', 'column' => 'group_price'],
            ['table' => 'catalog_product_index_price_tmp', 'column' => 'group_price'],
        ];

        foreach ($fields as $filedInfo) {
            $connection->dropColumn($setup->getTable($filedInfo['table']), $filedInfo['column']);
        }
    }

    /**
     * Add percentage value column
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addPercentageValueColumn(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('catalog_product_entity_tier_price');

        if (!$connection->tableColumnExists($tableName, 'percentage_value')) {
            $connection->addColumn(
                $tableName,
                'percentage_value',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '5,2',
                    'comment' => 'Percentage value',
                    'after' => 'value'
                ]
            );
        }
    }

    /**
     * Drop and recreate catalog_category_product_index_tmp table
     *
     * Before this update the catalog_category_product_index_tmp table was created without usage of PK
     * and with engine=MEMORY. Such structure of catalog_category_product_index_tmp table causes
     * issues with MySQL DB replication.
     *
     * To avoid replication issues this method drops catalog_category_product_index_tmp table
     * and creates new one with PK and engine=InnoDB
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function recreateCatalogCategoryProductIndexTmpTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('catalog_category_product_index_tmp');

        // Drop catalog_category_product_index_tmp table
        $setup->getConnection()->dropTable($tableName);

        // Create catalog_category_product_index_tmp table with PK
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Category ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Product ID'
            )
            ->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Position'
            )
            ->addColumn(
                'is_parent',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is Parent'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'visibility',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Visibility'
            )
            ->setOption(
                'type',
                \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
            )
            ->setComment('Catalog Category Product Indexer temporary table');

        $setup->getConnection()->createTable($table);
    }

    /**
     * Add key for the path field if not exists
     * significantly improves category tree performance
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addPathKeyToCategoryEntityTableIfNotExists(SchemaSetupInterface $setup)
    {
        /**
         * @var \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('catalog_category_entity');

        $keyName = $setup->getIdxName(
            $tableName,
            ['path'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
        );

        $existingKeys = $connection->getIndexList($tableName);
        if (!array_key_exists($keyName, $existingKeys)) {
            $connection->addIndex(
                $tableName,
                $keyName,
                ['path'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
        }
    }

    /**
     * Add the replica table for existing one.
     *
     * @param SchemaSetupInterface $setup
     * @param string $existingTable
     * @param string $replicaTable
     * @return void
     */
    private function addReplicaTable(SchemaSetupInterface $setup, $existingTable, $replicaTable)
    {
        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS %s LIKE %s',
            $setup->getConnection()->quoteIdentifier($setup->getTable($replicaTable)),
            $setup->getConnection()->quoteIdentifier($setup->getTable($existingTable))
        );
        $setup->getConnection()->query($sql);
    }
}
