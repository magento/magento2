<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Setup\Patch\Data;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConversionException;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Convert serialized wishlist item data.
 */
class ConvertSerializedData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * ConvertSerializedData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @param QueryModifierFactory $queryModifierFactory
     * @param QueryGenerator $queryGenerator
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly FieldDataConverterFactory $fieldDataConverterFactory,
        private readonly QueryModifierFactory $queryModifierFactory,
        private readonly QueryGenerator $queryGenerator
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->convertSerializedData();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Convert serialized whishlist item data.
     *
     * @throws FieldDataConversionException
     * @throws LocalizedException
     */
    private function convertSerializedData()
    {
        $connection = $this->moduleDataSetup->getConnection();
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
            $connection,
            $this->moduleDataSetup->getTable('wishlist_item_option'),
            'option_id',
            'value',
            $queryModifier
        );
        $select = $connection
            ->select()
            ->from(
                $this->moduleDataSetup->getTable('catalog_product_option'),
                ['option_id']
            )
            ->where('type = ?', 'file');
        $iterator = $this->queryGenerator->generate('option_id', $select);
        foreach ($iterator as $selectByRange) {
            $codes = $connection->fetchCol($selectByRange);
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
                $connection,
                $this->moduleDataSetup->getTable('wishlist_item_option'),
                'option_id',
                'value',
                $queryModifier
            );
        }
    }
}
