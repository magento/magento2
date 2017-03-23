<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Setup;

use Magento\Framework\Setup\ExternalFKSetup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * @codeCoverageIgnore
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ExternalFKSetup
     */
    protected $externalFKSetup;

    /**
     * @param MetadataPool $metadataPool
     * @param ExternalFKSetup $externalFKSetup
     */
    public function __construct(
        MetadataPool $metadataPool,
        ExternalFKSetup $externalFKSetup
    ) {
        $this->metadataPool = $metadataPool;
        $this->externalFKSetup = $externalFKSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $listTables = [
            ['product_alert_price', 'product_id'],
            ['product_alert_stock', 'product_id'],
        ];
        foreach ($listTables as $tableData) {
            $this->addExternalForeignKeys($installer, $tableData[0], $tableData[1]);
        }

        $installer->endSetup();
    }

    /**
     * Add external foreign keys
     *
     * @param SchemaSetupInterface $installer
     * @param string $tableName
     * @param string $columnName
     * @return void
     * @throws \Exception
     */
    protected function addExternalForeignKeys(SchemaSetupInterface $installer, $tableName, $columnName)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $this->externalFKSetup->install(
            $installer,
            $metadata->getEntityTable(),
            $metadata->getIdentifierField(),
            $tableName,
            $columnName
        );
    }
}
