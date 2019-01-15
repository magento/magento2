<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Setup;

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
            'report_viewed_product_aggregated_daily' => 'product_id',
            'report_viewed_product_aggregated_monthly' => 'product_id',
            'report_viewed_product_aggregated_yearly' => 'product_id',
            'report_compared_product_index' => 'product_id',
            'report_viewed_product_index' => 'product_id'
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
