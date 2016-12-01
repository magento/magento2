<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup;

use Magento\Framework\Serialize\Serializer\Json;
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
    protected $salesSetupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Json $serializer
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        Json $serializer
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $salesSetup->updateEntityType(
                \Magento\Sales\Model\Order::ENTITY,
                'entity_model',
                \Magento\Sales\Model\ResourceModel\Order::class
            );
            $salesSetup->updateEntityType(
                \Magento\Sales\Model\Order::ENTITY,
                'increment_model',
                \Magento\Eav\Model\Entity\Increment\NumericValue::class
            );
            $salesSetup->updateEntityType(
                'invoice',
                'entity_model',
                \Magento\Sales\Model\ResourceModel\Order::class
            );
            $salesSetup->updateEntityType(
                'invoice',
                'increment_model',
                \Magento\Eav\Model\Entity\Increment\NumericValue::class
            );
            $salesSetup->updateEntityType(
                'creditmemo',
                'entity_model',
                \Magento\Sales\Model\ResourceModel\Order\Creditmemo::class
            );
            $salesSetup->updateEntityType(
                'creditmemo',
                'increment_model',
                \Magento\Eav\Model\Entity\Increment\NumericValue::class
            );
            $salesSetup->updateEntityType(
                'shipment',
                'entity_model',
                \Magento\Sales\Model\ResourceModel\Order\Shipment::class
            );
            $salesSetup->updateEntityType(
                'shipment',
                'increment_model',
                \Magento\Eav\Model\Entity\Increment\NumericValue::class
            );
        }

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->upgradeVersionTwoZeroFive($setup);
        }

        $this->eavConfig->clear();
        $setup->endSetup();
    }

    /**
     * Upgrade version 2.0.5
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function upgradeVersionTwoZeroFive(ModuleDataSetupInterface $setup)
    {
        $orderItemTable = $setup->getTable('sales_order_item');

        $select = $setup->getConnection()->select()->from(
            $orderItemTable,
            ['item_id', 'product_options']
        )->where('product_options is not null');

        $orderItems = $setup->getConnection()->fetchAll($select);
        foreach ($orderItems as $orderItem) {
            $bind = ['product_options' => $this->convertData($orderItem['product_options'])];
            $where = ['item_id = ?' => (int)$orderItem['item_id']];
            $setup->getConnection()->update($orderItemTable, $bind, $where);
        }
    }

    /**
     * Convert serialized data to json string
     *
     * @param string $data
     * @return string
     */
    private function convertData($data)
    {
        return $this->serializer->serialize(unserialize($data));
    }
}
