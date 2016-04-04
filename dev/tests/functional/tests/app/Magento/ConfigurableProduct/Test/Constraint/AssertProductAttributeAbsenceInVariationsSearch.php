<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config as VariationsTab;
use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config\Attribute as AttributeBlock;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that deleted attribute can't be added to attribute set on Product Page via Add Attribute control.
 */
class AssertProductAttributeAbsenceInVariationsSearch extends AbstractConstraint
{
    /**
     * Label "Variations" tab.
     */
    const TAB_VARIATIONS = 'variations';

    /**
     * Assert that deleted attribute can't be added to attribute set on Product Page via Add Attribute control.
     *
     * @param CatalogProductAttribute $productAttribute
     * @param ConfigurableProduct $assertProduct
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @return void
     */
    public function processAssert(
        CatalogProductAttribute $productAttribute,
        ConfigurableProduct $assertProduct,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage
    ) {
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');

        /** @var VariationsTab $variationsTab */
        $newProductPage->getProductForm()->fill($assertProduct);
        $variationsTab = $newProductPage->getProductForm()->getSection(self::TAB_VARIATIONS);
        $variationsTab->createConfigurations();
        $attributesGrid = $variationsTab->getAttributeBlock()->getAttributesGrid();
        \PHPUnit_Framework_Assert::assertFalse(
            $attributesGrid->isRowVisible(['frontend_label' => $productAttribute->getFrontendLabel()]),
            "Product attribute found in Attribute Search form."
        );
    }

    /**
     * Text absent Product Attribute in Attribute Search form.
     *
     * @return string
     */
    public function toString()
    {
        return "Product Attribute is absent in Attribute Search form.";
    }
}
