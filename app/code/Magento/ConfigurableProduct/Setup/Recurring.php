<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Setup;

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

        $this->addExternalForeignKeys($installer);

        $installer->endSetup();
    }

    /**
     * Add external foreign keys
     *
     * @param SchemaSetupInterface $installer
     * @return void
     * @throws \Exception
     */
    protected function addExternalForeignKeys(SchemaSetupInterface $installer)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $this->externalFKSetup->install(
            $installer,
            $metadata->getEntityTable(),
            $metadata->getIdentifierField(),
            'catalog_product_super_link',
            'product_id'
        );
    }
}
