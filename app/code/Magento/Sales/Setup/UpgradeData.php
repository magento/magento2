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
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param SalesSetupFactory $salesSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->serializer = $serializer;
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
        $this->changeFieldFormat($setup, 'sales_order_item', 'item_id', 'product_options');
        $this->changeFieldFormat($setup, 'quote_payment', 'payment_id', 'additional_information');
        $this->changeFieldFormat($setup, 'sales_order_payment', 'entity_id', 'additional_information');
    }

    /**
     * Change format of the field for the table
     *
     * @param ModuleDataSetupInterface $setup
     * @param string $tableName
     * @param string $identifier
     * @param string $field
     * @return void
     */
    private function changeFieldFormat(ModuleDataSetupInterface $setup, $tableName, $identifier, $field)
    {
        $table = $setup->getTable($tableName);
        $select = $setup->getConnection()
            ->select()
            ->from($table, [$identifier, $field])
            ->where($field . ' IS NOT NULL');
        $items = $setup->getConnection()->fetchAll($select);
        foreach ($items as $item) {
            $bind = [$field => $this->convertData($item[$field])];
            $where = [$identifier . ' = ?' => (int) $item[$identifier]];
            $setup->getConnection()->update($table, $bind, $where);
        }
    }

    /**
     * Convert from serialized to json format
     *
     * @param string $data
     * @return string
     */
    private function convertData($data)
    {
        return $this->serializer->serialize(unserialize($data));
    }
}
