<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Setup;

use Magento\Framework\Setup\ExternalFKSetup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * @codeCoverageIgnore
 * @since 2.1.0
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @var ExternalFKSetup
     * @since 2.1.0
     */
    protected $externalFKSetup;

    /**
     * @param MetadataPool $metadataPool
     * @param ExternalFKSetup $externalFKSetup
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $listTables = [
            'catalog_product_bundle_price_index' => 'entity_id',
            'catalog_product_bundle_selection' => 'product_id',
        ];
        foreach ($listTables as $tableName => $columnName) {
            $this->addExternalForeignKeys($installer, $tableName, $columnName);
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
     * @since 2.1.0
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
