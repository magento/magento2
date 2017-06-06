<?php

namespace Liip\CustomerHierarchy\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    const CUSTOMER_HIERARCHY_TABLE = 'liip_customer_hierarchy';
    const CUSTOMER_PERMISSIONS_TABLE = 'liip_customer_permissions';
    const CUSTOMER_ROLES_TABLE = 'liip_customer_roles';
    const CUSTOMER_ROLES_ACCOUNTS_TABLE = 'liip_customer_roles_accounts';

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $contextInterface)
    {
        $setup->startSetup();

        $this->createHierarchyTable($setup);
        $this->createRolesTable($setup);
        $this->createPermissionsTable($setup);
        $this->createRolesToAccountsTable($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createHierarchyTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(self::CUSTOMER_HIERARCHY_TABLE))
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Customer ID'
            )
            ->addColumn(
                'parent_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Parent Customer ID'
            )
            ->addForeignKey(
                $setup->getFkName(
                    self::CUSTOMER_HIERARCHY_TABLE,
                    'customer_id',
                    $setup->getTable('customer_entity'),
                    'entity_id'
                ),
                'customer_id',
                $setup->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    self::CUSTOMER_HIERARCHY_TABLE,
                    'parent_id',
                    $setup->getTable('customer_entity'),
                    'entity_id'
                ),
                'parent_id',
                $setup->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Customer Hierarchy Table');

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createRolesTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(self::CUSTOMER_ROLES_TABLE))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Role ID'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Role Name'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->setComment('Customer Roles Table');

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createPermissionsTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(self::CUSTOMER_PERMISSIONS_TABLE))
            ->addColumn(
                'role_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Role ID'
            )
            ->addColumn(
                'code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Permission XPath'
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Permission Value'
            )
            ->addForeignKey(
                $setup->getFkName(
                    self::CUSTOMER_PERMISSIONS_TABLE,
                    'role_id',
                    $setup->getTable(self::CUSTOMER_ROLES_TABLE),
                    'entity_id'
                ),
                'role_id',
                $setup->getTable(self::CUSTOMER_ROLES_TABLE),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Customer Permissions Table');

        $setup->getConnection()->createTable($table);
    }

    private function createRolesToAccountsTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(self::CUSTOMER_ROLES_ACCOUNTS_TABLE))
            ->addColumn(
                'role_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Role ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Customer ID'
            )
            ->addForeignKey(
                $setup->getFkName(
                    self::CUSTOMER_ROLES_ACCOUNTS_TABLE,
                    'role_id',
                    $setup->getTable(self::CUSTOMER_ROLES_TABLE),
                    'entity_id'
                ),
                'role_id',
                $setup->getTable(self::CUSTOMER_ROLES_TABLE),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Roles-Accounts Table');

        $setup->getConnection()->createTable($table);
    }
}
