<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config as VariationsTab;
use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute as AttributeBlock;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductAttributeAbsenceInVariationsSearch
 * Check that deleted attribute can't be added to product template on Product Page via Add Attribute control
 */
class AssertProductAttributeAbsenceInVariationsSearch extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Label "Variations" tab
     */
    const TAB_VARIATIONS = 'variations';

    /**
     * Assert that deleted attribute can't be added to product template on Product Page via Add Attribute control
     *
     * @param CatalogProductAttribute $productAttribute
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @return void
     */
    public function processAssert(
        CatalogProductAttribute $productAttribute,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage
    ) {
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');

        /** @var VariationsTab $variationsTab */
        $variationsTab = $newProductPage->getProductForm()->getTabElement(self::TAB_VARIATIONS);
        $variationsTab->showContent();
        /** @var AttributeBlock $attributesBlock */
        $attributesBlock = $variationsTab->getAttributeBlock();
        \PHPUnit_Framework_Assert::assertFalse(
            $attributesBlock->getAttributeSelector()->isExistAttributeInSearchResult($productAttribute),
            "Product attribute found in Attribute Search form."
        );
    }

    /**
     * Text absent Product Attribute in Attribute Search form
     *
     * @return string
     */
    public function toString()
    {
        return "Product Attribute is absent in Attribute Search form.";
    }
}
