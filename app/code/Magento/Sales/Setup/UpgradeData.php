<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Setup;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;

/**
 * Data upgrade script
 */
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
     * @var \Magento\Sales\Setup\ConvertSerializedDataToJsonFactory
     */
    private $convertSerializedDataToJsonFactory;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
     * @param \Magento\Sales\Setup\ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory,
        \Magento\Sales\Setup\ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory,
        \Magento\Eav\Model\Config $eavConfig,
        FieldDataConverterFactory $fieldDataConverterFactory = null
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->convertSerializedDataToJsonFactory = $convertSerializedDataToJsonFactory;
        $this->eavConfig = $eavConfig;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory ?: ObjectManager::getInstance()
            ->get(FieldDataConverterFactory::class);
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
            $this->convertSerializedDataToJsonFactory->create(['salesSetup' => $salesSetup])
                ->convert();
        }
        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
            $fieldDataConverter->convert(
                $salesSetup->getConnection(),
                $salesSetup->getTable('sales_invoice_item'),
                'entity_id',
                'tax_ratio'
            );
            $fieldDataConverter->convert(
                $salesSetup->getConnection(),
                $salesSetup->getTable('sales_creditmemo_item'),
                'entity_id',
                'tax_ratio'
            );
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
}
