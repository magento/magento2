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
        $options[] = $attribute->getFrontendLabel();
        foreach ($attributeOptions as $option) {
            $options[] = $option['admin'];
        }
        $productAttributeOptions = $productEdit->getProductForm()->getAttributeElement($attribute)->getText();
        $productOptions = explode("\n", $productAttributeOptions);
        $diff = array_diff($options, $productOptions);

        \PHPUnit_Framework_Assert::assertTrue(
            empty($diff),
            "Products attribute options are absent on product form: " . implode(', ', $diff)
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'All product attribute options are visible on product creation form.';
    }
}
