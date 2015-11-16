<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config as TabVariation;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert check whether the attribute is used to create a configurable products.
 */
class AssertProductAttributeIsConfigurable extends AbstractConstraint
{
    /**
     * Assert check whether the attribute is used to create a configurable products.
     *
     * @param CatalogProductAttribute $attribute
     * @param ConfigurableProduct $assertProduct
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     */
    public function processAssert(
        CatalogProductAttribute $attribute,
        ConfigurableProduct $assertProduct,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage
    ) {
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('configurable');
        $productBlockForm = $newProductPage->getProductForm();
        $productBlockForm->fill($assertProduct);
        $productBlockForm->openTab('variations');
        /** @var \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config  $variationsTab */
        $variationsTab = $productBlockForm->getTab('variations');
        $variationsTab->createConfigurations();
        $attributesGrid = $variationsTab->getAttributeBlock()->getAttributesGrid();
        \PHPUnit_Framework_Assert::assertTrue(
            $attributesGrid->isRowVisible(['frontend_label' => $attribute->getFrontendLabel()]),
            "Product attribute is absent on the product page."
        );
    }

    /**
     * Attribute label present on the product page in variations section.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute label present on the product page in variations section.';
    }
}
