<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\DB\Select\InQueryModifier;
use Magento\Framework\DB\Query\Generator;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var Generator
     */
    private $queryGenerator;

    /**
     * Constructor
     *
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @param QueryModifierFactory $queryModifierFactory
     * @param Generator $queryGenerator
     */
    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory,
        QueryModifierFactory $queryModifierFactory,
        Generator $queryGenerator
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->queryGenerator = $queryGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->upgradeToVersionTwoZeroOne($setup);
        }
    }

    /**
     * Upgrade to version 2.0.1, convert data for `value` field in `wishlist_item_option table`
     * from php-serialized to JSON format
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeToVersionTwoZeroOne(ModuleDataSetupInterface $setup)
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'code' => [
                        'parameters',
                        'info_buyRequest',
                        'bundle_option_ids',
                        'bundle_selection_ids',
                        'attributes',
                        'bundle_selection_attributes',
                    ]
                ]
            ]
        );
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('wishlist_item_option'),
            'option_id',
            'value',
            $queryModifier
        );
        $select = $setup->getConnection()
            ->select()
            ->from(
                $setup->getTable('catalog_product_option'),
                ['option_id']
            )
            ->where('type = ?', 'file');
        $iterator = $this->queryGenerator->generate('option_id', $select);
        foreach ($iterator as $selectByRange) {
            $codes = $setup->getConnection()->fetchCol($selectByRange);
            $codes = array_map(
                function ($id) {
                    return 'option_' . $id;
                },
                $codes
            );
            $queryModifier = $this->queryModifierFactory->create(
                'in',
                [
                    'values' => [
                        'code' => $codes
                    ]
                ]
            );
            $fieldDataConverter->convert(
                $setup->getConnection(),
                $setup->getTable('wishlist_item_option'),
                'option_id',
                'value',
                $queryModifier
            );
        }
    }
}
