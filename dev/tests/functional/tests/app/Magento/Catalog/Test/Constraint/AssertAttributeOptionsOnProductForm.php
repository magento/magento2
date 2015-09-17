<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Assert all product attribute options on product creation form.
 */
class AssertAttributeOptionsOnProductForm extends AbstractConstraint
{
    /**
     * Assert all product attribute options on product creation form.
     *
     * @param InjectableFixture $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductAttribute $attribute
     * @param CatalogProductEdit $productEdit
     * @return void
     */
    public function processAssert(
        InjectableFixture $product,
        CatalogProductIndex $productGrid,
        CatalogProductAttribute $attribute,
        CatalogProductEdit $productEdit
    ) {
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen(['sku' => $product->getSku()]);

        $attributeOptions = $attribute->getOptions();
        $productAttributeOptions = $productEdit->getProductForm()->getAttributeElement($attribute)->getValue();
        $optionsVisible = true;
        foreach ($attributeOptions as $option) {
            if (array_search($option['admin'], $productAttributeOptions) === false) {
                $optionsVisible = false;
                break;
            }
        }

        \PHPUnit_Framework_Assert::assertTrue($optionsVisible, "Product Attribute is absent on Product form.");
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product attribute options is visible on product creation form.';
    }
}
