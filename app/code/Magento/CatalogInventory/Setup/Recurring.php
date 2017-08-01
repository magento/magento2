<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Setup;

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

        $this->addExternalForeignKeys($installer);

        $installer->endSetup();
    }

    /**
     * Add external foreign keys
     *
     * @param SchemaSetupInterface $installer
     * @return void
     * @throws \Exception
     * @since 2.1.0
     */
    protected function addExternalForeignKeys(SchemaSetupInterface $installer)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $this->externalFKSetup->install(
            $installer,
            $metadata->getEntityTable(),
            $metadata->getIdentifierField(),
            'cataloginventory_stock_item',
            'product_id'
        );
    }
}
