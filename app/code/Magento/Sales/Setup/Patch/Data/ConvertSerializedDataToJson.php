<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Framework\App\State;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Setup\SalesOrderPaymentDataConverter;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Sales\Setup\SerializedDataConverter;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class ConvertSerializedDataToJson
 * @package Magento\Sales\Setup\Patch
 */
class ConvertSerializedDataToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldDataConverter;

    /**
     * PatchInitial constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $salesSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        AggregatedFieldDataConverter $aggregatedFieldDataConverter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->aggregatedFieldDataConverter = $aggregatedFieldDataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create();
        $this->convertSerializedDataToJson($salesSetup);
        $this->eavConfig->clear();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateEntityTypes::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.6';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Convert native serialization to JSON.
     *
     * @param SalesSetup $salesSetup
     */
    private function convertSerializedDataToJson(SalesSetup $salesSetup)
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
        $this->aggregatedFieldDataConverter->convert($fieldsToUpdate, $salesSetup->getConnection());
    }
}
