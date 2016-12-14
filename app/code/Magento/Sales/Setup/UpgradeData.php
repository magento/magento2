<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Setup;

use Magento\Sales\Model\Order\Item\Converter\ProductOptions\SerializedToJson;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * Sales setup factory
     *
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\DB\FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->upgradeToTwoZeroOne($salesSetup);
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->upgradeToVersionTwoZeroFive($setup);
        }
        $this->eavConfig->clear();
    }

    /**
     * Upgrade to version 2.0.1
     *
     * @param \Magento\Sales\Setup\SalesSetup $setup
     * @return void
     */
    private function upgradeToTwoZeroOne(\Magento\Sales\Setup\SalesSetup $setup)
    {
        $setup->updateEntityType(
            \Magento\Sales\Model\Order::ENTITY,
            'entity_model',
            \Magento\Sales\Model\ResourceModel\Order::class
        );
        $setup->updateEntityType(
            \Magento\Sales\Model\Order::ENTITY,
            'increment_model',
            \Magento\Eav\Model\Entity\Increment\NumericValue::class
        );
        $setup->updateEntityType(
            'invoice',
            'entity_model',
            \Magento\Sales\Model\ResourceModel\Order::class
        );
        $setup->updateEntityType(
            'invoice',
            'increment_model',
            \Magento\Eav\Model\Entity\Increment\NumericValue::class
        );
        $setup->updateEntityType(
            'creditmemo',
            'entity_model',
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo::class
        );
        $setup->updateEntityType(
            'creditmemo',
            'increment_model',
            \Magento\Eav\Model\Entity\Increment\NumericValue::class
        );
        $setup->updateEntityType(
            'shipment',
            'entity_model',
            \Magento\Sales\Model\ResourceModel\Order\Shipment::class
        );
        $setup->updateEntityType(
            'shipment',
            'increment_model',
            \Magento\Eav\Model\Entity\Increment\NumericValue::class
        );
    }

    /**
     * Upgrade to version 2.0.5, convert data for the following fields from serialized to JSON format:
     * sales_order_item.product_options
     * sales_shipment.packages
     * sales_order_payment.additional_information
     * sales_payment_transaction.additional_information
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeToVersionTwoZeroFive(\Magento\Framework\Setup\ModuleDataSetupInterface $setup)
    {
        $productOptionsDataConverter = $this->fieldDataConverterFactory->create(
            SerializedToJson::class
        );
        $productOptionsDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('sales_order_item'),
            'item_id',
            'product_options'
        );
        $fieldDataConverter = $this->fieldDataConverterFactory->create(
            \Magento\Framework\DB\DataConverter\SerializedToJson::class
        );
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('sales_shipment'),
            'entity_id',
            'packages'
        );
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('sales_order_payment'),
            'entity_id',
            'additional_information'
        );
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('sales_payment_transaction'),
            'transaction_id',
            'additional_information'
        );
    }
}
