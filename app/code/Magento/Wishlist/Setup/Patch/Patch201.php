<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Setup\Patch;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201
{


    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    private $fieldDataConverterFactory;
    /**
     * @param QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;
    /**
     * @param Generator $queryGenerator
     */
    private $queryGenerator;
    /**
     * @param QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory @param QueryModifierFactory $queryModifierFactory@param Generator $queryGenerator@param QueryModifierFactory $queryModifierFactory
     */
    public function __construct(FieldDataConverterFactory $fieldDataConverterFactory,
                                QueryModifierFactory $queryModifierFactory,
                                Generator $queryGenerator

        ,
                                QueryModifierFactory $queryModifierFactory)
    {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->queryGenerator = $queryGenerator;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->upgradeToVersionTwoZeroOne($setup);

    }

    private function upgradeToVersionTwoZeroOne(ModuleDataSetupInterface $setup
    )
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

}
}
