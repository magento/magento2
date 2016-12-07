<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
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
     * @param SalesSetupFactory $salesSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
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
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->upgradeToTwoZeroOne($salesSetup);
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->upgradeToVersionTwoZeroFive($setup);
        }
        $this->eavConfig->clear();
        $setup->endSetup();
    }

    /**
     * Upgrade to version 2.0.1
     *
     * @param SalesSetup $setup
     * @return void
     */
    private function upgradeToTwoZeroOne(SalesSetup $setup)
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
     * Upgrade to version 2.0.5
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeToVersionTwoZeroFive(ModuleDataSetupInterface $setup)
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(
            \Magento\Framework\DB\DataConverter\SerializedToJson::class
        );
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('sales_order_item'),
            'item_id',
            'product_options'
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
