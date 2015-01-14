<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\InjectableFixture;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Block\Adminhtml\Product\ProductForm;

/**
 * Check whether the attribute filter is displayed on the frontend in Layered navigation.
 */
class AssertProductAttributeIsFilterable extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Check whether the attribute filter is displayed on the frontend in Layered navigation.
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param InjectableFixture $product
     * @param CatalogProductAttribute $attribute
     * @param CmsIndex $cmsIndex
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        InjectableFixture $product,
        CatalogProductAttribute $attribute,
        CmsIndex $cmsIndex,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        FixtureFactory $fixtureFactory
    ) {
        $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataSet' => 'product_with_category_with_anchor',
                'data' => [
                    'category_ids' => [
                        'presets' => null,
                        'category' => $product->getDataFieldConfig('category_ids')['source']->getCategories()[0]
                    ]
                ],
            ]
        )->persist();

        $catalogProductIndex->open()->getProductGrid()->searchAndOpen(['sku' => $product->getSku()]);
        $productForm = $catalogProductEdit->getProductForm();
        $this->setDefaultAttributeValue($productForm, $attribute);
        $catalogProductEdit->getFormPageActions()->save();
        $cmsIndex->open()->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);
        $label = $attribute->hasData('manage_frontend_label')
            ? $attribute->getManageFrontendLabel()
            : $attribute->getFrontendLabel();
        \PHPUnit_Framework_Assert::assertTrue(
            in_array($label, $catalogCategoryView->getLayeredNavigationBlock()->getFilters()),
            'Attribute is absent in layered navigation on category page.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is present in layered navigation on category page.';
    }

    /**
     * Set default attribute value.
     *
     * @param ProductForm $productForm
     * @param CatalogProductAttribute $attribute
     * @return void
     */
    protected function setDefaultAttributeValue(ProductForm $productForm, CatalogProductAttribute $attribute)
    {
        $attributeData = $attribute->getData();
        if (isset($attributeData['options'])) {
            foreach ($attributeData['options'] as $option) {
                if ($option['is_default'] == 'Yes') {
                    $defaultValue = $option['admin'];
                }
            }
        } else {
            $field = preg_grep('@^default_value@', array_keys($attributeData));
            $defaultValue = $attributeData[array_shift($field)];
        }
        $productForm->getAttributeElement($attribute)->setValue($defaultValue);
    }
}
