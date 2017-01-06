<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Setup;

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
     * Constructor
     *
     * @param \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
     * @param \Magento\Sales\Setup\ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory,
        \Magento\Sales\Setup\ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->convertSerializedDataToJsonFactory = $convertSerializedDataToJsonFactory;
        $this->eavConfig = $eavConfig;
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
