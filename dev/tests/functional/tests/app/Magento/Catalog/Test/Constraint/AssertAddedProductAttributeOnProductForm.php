<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\ObjectManager;

/**
 * Check attribute on product form.
 */
class AssertAddedProductAttributeOnProductForm extends AbstractConstraint
{
    /**
     * Add this attribute to Default attribute Template. Create product and Assert that created attribute
     * is displayed on product form (Products > Inventory > Catalog).
     *
     * @param InjectableFixture $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productEdit
     * @param CatalogProductAttribute $attribute
     * @param CatalogAttributeSet $attributeSet
     * @param CatalogProductAttribute $productAttributeOriginal
     * @throws \Exception
     * @return void
     */
    public function processAssert(
        InjectableFixture $product,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productEdit,
        CatalogProductAttribute $attribute,
        CatalogAttributeSet $attributeSet,
        CatalogProductAttribute $productAttributeOriginal = null
    ) {
        if (!$product->hasData('sku')) {
            $product = $this->createProductWithAttributeSet($productAttributeOriginal, $attributeSet);
        }
        $filterProduct = ['sku' => $product->getSku()];
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen($filterProduct);

        $catalogProductAttribute = ($productAttributeOriginal !== null)
            ? array_merge($productAttributeOriginal->getData(), $attribute->getData())
            : $attribute->getData();

        \PHPUnit_Framework_Assert::assertTrue(
            $productEdit->getProductForm()->checkAttributeLabel($catalogProductAttribute),
            "Product Attribute is absent on Product form."
        );
    }

    /**
     * Create Product With AttributeSet.
     *
     * @param CatalogProductAttribute $attribute
     * @param CatalogAttributeSet $attributeSet
     * @return CatalogProductSimple
     */
    protected function createProductWithAttributeSet(
        CatalogProductAttribute $attribute,
        CatalogAttributeSet $attributeSet
    ) {
        $product = ObjectManager::getInstance()->create(
            'Magento\Catalog\Test\TestStep\AddAttributeToAttributeSetStep',
            ['attribute' => $attribute, 'attributeSet' => $attributeSet]
        )->run();
        return $product['product'];
    }

    /**
     * Text of Product Attribute is present on the Product form.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Attribute is present on Product form.';
    }
}
