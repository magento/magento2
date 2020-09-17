<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category\Bundle;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Module\Manager;
use Magento\LayeredNavigation\Block\Navigation\AbstractFiltersTest;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Provides tests for custom multiselect filter in navigation block on category page with bundle products.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class MultiselectFilterTest extends AbstractFiltersTest
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->moduleManager = $this->objectManager->get(Manager::class);
        //This check is needed because LayeredNavigation independent of Magento_Bundle
        if (!$this->moduleManager->isEnabled('Magento_Bundle')) {
            $this->markTestSkipped('Magento_Bundle module disabled.');
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
     * @magentoDataFixture Magento/Bundle/_files/dynamic_and_fixed_bundle_products_in_category.php
     * @dataProvider getFiltersWithCustomAttributeDataProvider
     * @param array $products
     * @param array $attributeData
     * @param array $expectation
     * @return void
     */
    public function testGetFiltersWithCustomAttribute(array $products, array $attributeData, array $expectation): void
    {
        $this->getCategoryFiltersAndAssert($products, $attributeData, $expectation, 'Category 1');
    }

    /**
     * @return array
     */
    public function getFiltersWithCustomAttributeDataProvider(): array
    {
        return [
            'not_used_in_navigation' => [
                'products_data' => [],
                'attribute_data' => ['is_filterable' => 0],
                'expectation' => [],
            ],
            'used_in_navigation_with_results' => [
                'products_data' => [
                    'bundle-product' => 'Option 1',
                    'bundle-product-dropdown-options' => 'Option 2',
                ],
                'attribute_data' => ['is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS],
                'expectation' => [
                    ['label' => 'Option 1', 'count' => 1],
                    ['label' => 'Option 2', 'count' => 1],
                ],
            ],
            'used_in_navigation_without_results' => [
                'products_data' => [
                    'bundle-product' => 'Option 1',
                    'bundle-product-dropdown-options' => 'Option 2',
                ],
                'attribute_data' => ['is_filterable' => 2],
                'expectation' => [
                    ['label' => 'Option 1', 'count' => 1],
                    ['label' => 'Option 2', 'count' => 1],
                    ['label' => 'Option 3', 'count' => 0],
                    ['label' => 'Option 4 "!@#$%^&*', 'count' => 0],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getLayerType(): string
    {
        return Resolver::CATALOG_LAYER_CATEGORY;
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'multiselect_attribute';
    }
}
