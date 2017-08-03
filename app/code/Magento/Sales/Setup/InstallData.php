<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\Config as SequenceConfig;

/**
 * Class InstallData
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallData implements InstallDataInterface
{
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     * @since 2.0.0
     */
    private $salesSetupFactory;

    /**
     * @var Builder
     * @since 2.0.0
     */
    private $sequenceBuilder;

    /**
     * @var SequenceConfig
     * @since 2.0.0
     */
    private $sequenceConfig;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param Builder $sequenceBuilder
     * @param SequenceConfig $sequenceConfig
     * @since 2.0.0
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        Builder $sequenceBuilder,
        SequenceConfig $sequenceConfig
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        /**
         * Install eav entity types to the eav/entity_type table
         */
        $salesSetup->installEntities();

        /**
         * Install order statuses from config
         */
        $data = [];
        $statuses = [
            'pending' => __('Pending'),
            'pending_payment' => __('Pending Payment'),
            'processing' => __('Processing'),
            'holded' => __('On Hold'),
            'complete' => __('Complete'),
            'closed' => __('Closed'),
            'canceled' => __('Canceled'),
            'fraud' => __('Suspected Fraud'),
            'payment_review' => __('Payment Review'),
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $setup->getConnection()->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);

        /**
         * Install order states from config
         */
        $data = [];
        $states = [
            'new' => [
                'label' => __('New'),
                'statuses' => ['pending' => ['default' => '1']],
                'visible_on_front' => true,
            ],
            'pending_payment' => [
                'label' => __('Pending Payment'),
                'statuses' => ['pending_payment' => ['default' => '1']],
            ],
            'processing' => [
                'label' => __('Processing'),
                'statuses' => ['processing' => ['default' => '1'], 'fraud' => []],
                'visible_on_front' => true,
            ],
            'complete' => [
                'label' => __('Complete'),
                'statuses' => ['complete' => ['default' => '1']],
                'visible_on_front' => true,
            ],
            'closed' => [
                'label' => __('Closed'),
                'statuses' => ['closed' => ['default' => '1']],
                'visible_on_front' => true,
            ],
            'canceled' => [
                'label' => __('Canceled'),
                'statuses' => ['canceled' => ['default' => '1']],
                'visible_on_front' => true,
            ],
            'holded' => [
                'label' => __('On Hold'),
                'statuses' => ['holded' => ['default' => '1']],
                'visible_on_front' => true,
            ],
            'payment_review' => [
                'label' => __('Payment Review'),
                'statuses' => ['payment_review' => ['default' => '1'], 'fraud' => []],
                'visible_on_front' => true,
            ],
        ];

        foreach ($states as $code => $info) {
            if (isset($info['statuses'])) {
                foreach ($info['statuses'] as $status => $statusInfo) {
                    $data[] = [
                        'status' => $status,
                        'state' => $code,
                        'is_default' => is_array($statusInfo) && isset($statusInfo['default']) ? 1 : 0,
                    ];
                }
            }
        }
        $setup->getConnection()->insertArray(
            $setup->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default'],
            $data
        );

        $entitiesToAlter = ['order_address'];

        $attributes = [
            'vat_id' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT],
            'vat_is_valid' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT],
            'vat_request_id' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT],
            'vat_request_date' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT],
            'vat_request_success' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT],
        ];

        foreach ($entitiesToAlter as $entityName) {
            foreach ($attributes as $attributeCode => $attributeParams) {
                $salesSetup->addAttribute($entityName, $attributeCode, $attributeParams);
            }
        }

        /** Update visibility for states */
        $states = ['new', 'processing', 'complete', 'closed', 'canceled', 'holded', 'payment_review'];
        foreach ($states as $state) {
            $setup->getConnection()->update(
                $setup->getTable('sales_order_status_state'),
                ['visible_on_front' => 1],
                ['state = ?' => $state]
            );
        }
    }
}
