<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config as VariationsTab;
use \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute as AttributeBlock;

/**
 * Class AssertProductAttributeAbsenceInVariationsSearch
 * Check that deleted attribute can't be added to product template on Product Page via Add Attribute control
 */
class AssertProductAttributeAbsenceInVariationsSearch extends AbstractConstraint
{
    /**
     * Label "Variations" tab
     */
    const TAB_VARIATIONS = 'variations';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that deleted attribute can't be added to product template on Product Page via Add Attribute control
     *
     * @param CatalogProductAttribute $productAttribute
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @return void
     */
    public function processAssert
    (
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
