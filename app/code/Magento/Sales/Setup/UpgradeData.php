<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Setup;

use Magento\Eav\Model\Config;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Data upgrade script
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AggregatedFieldDataConverter
     * @since 2.2.0
     */
    private $aggregatedFieldConverter;

    /**
     * Constructor
     *
     * @param SalesSetupFactory $salesSetupFactory
     * @param Config $eavConfig
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        Config $eavConfig,
        AggregatedFieldDataConverter $aggregatedFieldConverter
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $salesSetup->updateEntityTypes();
        }
        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $this->convertSerializedDataToJson($context->getVersion(), $salesSetup);
        }
        $this->eavConfig->clear();
    }

    /**
     * Convert data from serialized to JSON encoded
     *
     * @param string $setupVersion
     * @param SalesSetup $salesSetup
     * @return void
     * @since 2.2.0
     */
    private function convertSerializedDataToJson($setupVersion, SalesSetup $salesSetup)
    {
        $fieldsToUpdate = [
            new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_invoice_item'),
                'entity_id',
                'tax_ratio'
            ),
            new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_creditmemo_item'),
                'entity_id',
                'tax_ratio'
            ),
        ];
        if (version_compare($setupVersion, '2.0.5', '<')) {
            $fieldsToUpdate[] = new FieldToConvert(
                SerializedDataConverter::class,
                $salesSetup->getTable('sales_order_item'),
                'item_id',
                'product_options'
            );
            $fieldsToUpdate[] = new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_shipment'),
                'entity_id',
                'packages'
            );
            $fieldsToUpdate[] = new FieldToConvert(
                SalesOrderPaymentDataConverter::class,
                $salesSetup->getTable('sales_order_payment'),
                'entity_id',
                'additional_information'
            );
            $fieldsToUpdate[] = new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_payment_transaction'),
                'transaction_id',
                'additional_information'
            );
        }
        $this->aggregatedFieldConverter->convert($fieldsToUpdate, $salesSetup->getConnection());
    }
}
