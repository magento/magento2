<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config as SectionVariation;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Assert that only products in stock is presented in layered navigation.
 */
class AssertProductsInStockInLayeredNavigation extends AbstractConstraint
{
    /**
     * Assert that only products in stock is presented in layered navigation.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param ConfigurableProduct $product
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        ConfigurableProduct $product
    ) {
        $sourceCategory = $product->getDataFieldConfig('category_ids')['source']->getCategories()[0];

        // create simple product with the same category as configurable.
        $productSimple = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataset' => 'default',
                'data' => [
                    'category_ids' => [
                        'category' => $sourceCategory,
                    ],
                ]
            ]
        );
        $productSimple->persist();

        $categoryName = $product->getCategoryIds()[0];

        // open category on frontend.
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);

        // get configurable attribute name.
        $attributeName = '';
        foreach ($product->getConfigurableAttributesData()['attributes_data'] as $data) {
            $attributeName = $data['label'];
        }

        // get options which are visible.
        $visibleOptions =
            $catalogCategoryView->getLayeredNavigationBlock()->getOptionsContentForAttribute($attributeName);

        $expectedVisibleOptions = [];
        $attributesData = $product->getConfigurableAttributesData()['attributes_data'];

        // get options which shouldd be visible.
        foreach ($product->getConfigurableAttributesData()['matrix'] as $key => $value) {
            if ($value['qty'] > 0) {
                $attribute = explode(':', $key)[0];
                $option = explode(':', $key)[1];
                $expectedVisibleOptions[] = $attributesData[$attribute]['options'][$option]['label'];
            }
        }

        \PHPUnit_Framework_Assert::assertEquals($expectedVisibleOptions, $visibleOptions);
    }

    /**
     * Only products in stock is presented in layered navigation.
     *
     * @return string
     */
    public function toString()
    {
        return 'Only products in stock is presented in layered navigation';
    }
}
