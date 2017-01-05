<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    protected $salesSetupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
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
        $this->eavConfig->clear();
        $setup->endSetup();
    }
}
