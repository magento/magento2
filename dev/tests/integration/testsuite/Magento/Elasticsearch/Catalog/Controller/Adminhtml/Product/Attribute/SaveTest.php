<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Catalog\Controller\Adminhtml\Product\Attribute;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save\AbstractSaveAttributeTest;

/**
 * Test cases related to creating attribute and search engine.
 */
class SaveTest extends AbstractSaveAttributeTest
{
    /**
     * Default POST data for create attribute.
     *
     * @var array
     */
    private const DEFAULT_ATTRIBUTE_POST_DATA = [
        'active_tab' => 'main',
        'frontend_label' => [
            '0' => 'Test attribute name',
        ],
        'frontend_input' => 'select',
        'is_required' => '0',
        'attribute_code' => 'filtrable_attribute',
        'is_global' => '1',
        'default_value_yesno' => '0',
        'is_unique' => '0',
        'is_used_in_grid' => '1',
        'is_visible_in_grid' => '1',
        'is_filterable_in_grid' => '1',
        'is_filterable' => '1',
        'is_filterable_in_search' => '0',
        'is_searchable' => '0',
        'is_comparable' => '0',
        'is_used_for_promo_rules' => '0',
        'is_html_allowed_on_front' => '1',
        'is_visible_on_front' => '0',
        'used_in_product_listing' => '0',
    ];

    /**
     * @param array $data
     * @param string $errorMessage
     * @dataProvider createAttributeWithErrorDataProvider
     */
    public function testCreateAttributeWithError(array $data, string $errorMessage): void
    {
        $this->createAttributeUsingDataWithErrorAndAssert(
            array_merge(self::DEFAULT_ATTRIBUTE_POST_DATA, $data),
            $errorMessage
        );
    }

    /**
     * @return array
     */
    public static function createAttributeWithErrorDataProvider(): array
    {
        return [
            'should not create attribute with reserved code "category_name"' => [
                [
                    'attribute_code' => 'category_name'
                ],
                'The attribute code &#039;category_name&#039; is reserved by system. ' .
                'Please try another attribute code'
            ]
        ];
    }
}
