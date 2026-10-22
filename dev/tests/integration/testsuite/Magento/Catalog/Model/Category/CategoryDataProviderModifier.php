<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Category;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Test modifier for category data provider.
 */
class CategoryDataProviderModifier implements ModifierInterface
{
    public const META_KEY = 'category_form_test_modifier';

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $meta[self::META_KEY] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'fieldset'
                    ]
                ]
            ]
        ];

        return $meta;
    }
}
