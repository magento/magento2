<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 * @since 2.1.0
 */
class ExternalFKSetup
{
    /**
     * @var SchemaSetupInterface
     * @since 2.1.0
     */
    protected $setup;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $entityTable;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $entityColumn;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $externalTable;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $externalColumn;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $onDelete;

    /**
     * Install external foreign key
     *
     * @param SchemaSetupInterface $setup
     * @param string $entityTable
     * @param string $entityColumn
     * @param string $externalTable
     * @param string $externalColumn
     * @param string $onDelete
     * @return void
     * @since 2.1.0
     */
    public function install(
        SchemaSetupInterface $setup,
        $entityTable,
        $entityColumn,
        $externalTable,
        $externalColumn,
        $onDelete = AdapterInterface::FK_ACTION_CASCADE
    ) {
        $this->setup = $setup;
        $this->entityTable = $entityTable;
        $this->entityColumn = $entityColumn;
        $this->externalTable = $externalTable;
        $this->externalColumn = $externalColumn;
        $this->onDelete = $onDelete;

        $this->execute();
    }

    /**
     * Set external foreign key
     *
     * @return void
     * @since 2.1.0
     */
    protected function execute()
    {
        $entityTableInfo = $this->setup->getConnection()->describeTable(
            $this->setup->getTable($this->entityTable)
        );
        if (!$entityTableInfo[$this->entityColumn]['PRIMARY']) {
            $this->dropOldForeignKey();
            $this->addForeignKeys();
        } else {
            $this->addDefaultForeignKey();
        }
    }

    /**
     * Get foreign keys for tables and columns
     *
     * @param string $refTable
     * @param string $refColumn
     * @param string $targetTable
     * @param string $targetColumn
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    protected function getForeignKeys(
        $targetTable,
        $targetColumn,
        $refTable,
        $refColumn
    ) {
        $foreignKeys = $this->setup->getConnection()->getForeignKeys(
            $this->setup->getTable($targetTable)
        );
        $foreignKeys = array_filter(
            $foreignKeys,
            function ($key) use ($targetColumn, $refTable, $refColumn) {
                return $key['COLUMN_NAME'] == $targetColumn
                && $key['REF_TABLE_NAME'] == $refTable;
            }
        );
        return $foreignKeys;
    }

    /**
     * Remove foreign key if exists
     *
     * @param string $targetTable
     * @param string $targetColumn
     * @param string $refTable
     * @param string $refColumn
     * @return void
     * @since 2.1.0
     */
    protected function clearForeignKey(
        $targetTable,
        $targetColumn,
        $refTable,
        $refColumn
    ) {
        $foreignKeys = $this->getForeignKeys($targetTable, $targetColumn, $refTable, $refColumn);
        foreach ($foreignKeys as $foreignKey) {
            $this->setup->getConnection()->dropForeignKey(
                $foreignKey['TABLE_NAME'],
                $foreignKey['FK_NAME']
            );
        }
    }

    /**
     * Add default foreign key
     *
     * @return void
     * @since 2.1.0
     */
    protected function addDefaultForeignKey()
    {
        if (!count($this->getForeignKeys(
            $this->externalTable,
            $this->externalColumn,
            $this->entityTable,
            $this->entityColumn
        ))) {
            $this->setup->getConnection()->addForeignKey(
                $this->setup->getFkName(
                    $this->externalTable,
                    $this->externalColumn,
                    $this->entityTable,
                    $this->entityColumn
                ),
                $this->setup->getTable($this->externalTable),
                $this->externalColumn,
                $this->setup->getTable($this->entityTable),
                $this->entityColumn,
                $this->onDelete
            );
        }
    }

    /**
     * Add foreign keys to entity table
     *
     * @return void
     * @since 2.1.0
     */
    protected function addForeignKeys()
    {
        $foreignKeys = $this->setup->getConnection()->getForeignKeys(
            $this->setup->getTable($this->entityTable)
        );
        $foreignKeys = array_filter(
            $foreignKeys,
            function ($key) {
                return $key['COLUMN_NAME'] == $this->entityColumn;
            }
        );
        foreach ($foreignKeys as $foreignKeyInfo) {
            if (!count($this->getForeignKeys(
                $this->externalTable,
                $this->externalColumn,
                $this->setup->getTablePlaceholder($foreignKeyInfo['REF_TABLE_NAME']),
                $foreignKeyInfo['REF_COLUMN_NAME']
            ))) {
                $this->setup->getConnection()->addForeignKey(
                    $this->setup->getFkName(
                        $this->externalTable,
                        $this->externalColumn,
                        $this->setup->getTablePlaceholder($foreignKeyInfo['REF_TABLE_NAME']),
                        $foreignKeyInfo['REF_COLUMN_NAME']
                    ),
                    $this->setup->getTable($this->externalTable),
                    $this->externalColumn,
                    $foreignKeyInfo['REF_TABLE_NAME'],
                    $foreignKeyInfo['REF_COLUMN_NAME'],
                    $this->onDelete
                );
            }
        }
    }

    /**
     * Drop old foreign key
     *
     * @return void
     * @since 2.1.0
     */
    protected function dropOldForeignKey()
    {
        if (count($this->getForeignKeys(
            $this->externalTable,
            $this->externalColumn,
            $this->entityTable,
            $this->entityColumn
        ))) {
            $this->clearForeignKey(
                $this->externalTable,
                $this->externalColumn,
                $this->entityTable,
                $this->entityColumn
            );
        }
    }
}
