<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;

/**
 * Check whether the attribute filter is displayed on the frontend in Layered navigation.
 */
class AssertConfigurableProductAttributeOptionInLayeredNavigation extends AbstractConstraint
{

    /**
     * Get product attribute
     *
     * @param InjectableFixture $product
     * @return mixed

    private function getAttribute(InjectableFixture $product)
    {
        $attributes = $product->getDataFieldConfig('configurable_attributes_data')['source']->getAttributes();
        if (is_array($attributes)) {
            $attribute = current($attributes);
        }
        return isset($attribute) ? $attribute : null;
    }
     */

    /**
     * @param InjectableFixture $product
     */
    /**
    private function getOptions(InjectableFixture $product) {


        $attributesData = $product->hasData('configurable_attributes_data')
            ? $product->getConfigurableAttributesData()['attributes_data']
            : [];
        foreach($attributesData as $option) {
            $optionValue = $option[0]['view'];
        }

    }
    */

    /**
     * Check whether the attribute filter is displayed on the frontend in Layered navigation.
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param InjectableFixture $product
     * @param CatalogProductAttribute $attribute
     * @param CmsIndex $cmsIndex
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        InjectableFixture $product,
        CmsIndex $cmsIndex,
        FixtureFactory $fixtureFactory,
        $outOfStockOption
    ) {
        $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataset' => 'product_with_category_with_anchor',
                'data' => [
                    'category_ids' => [
                        'dataset' => null,
                        'category' => $product->getDataFieldConfig('category_ids')['source']->getCategories()[0]
                    ]
                ],
            ]
        )->persist();

        $cmsIndex->open()->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);
        /*$label = $attribute->hasData('manage_frontend_label')
            ? $attribute->getManageFrontendLabel()
            : $attribute->getFrontendLabel();
        $attributeValue = $this->getAttribute($product);
        if($attributeValue)
            $label = $attributeValue->getOptions()[0]['view'];
        */

        $filters = $catalogCategoryView->getLayeredNavigationBlock()->getFilterContents();

        \PHPUnit_Framework_Assert::assertFalse(
            in_array(strtoupper($outOfStockOption), $filters),
            'Out of Stock attribute option is present in layered navigation on category page.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Out of Stock attribute option is absent in layered navigation on category page.';
    }
}
