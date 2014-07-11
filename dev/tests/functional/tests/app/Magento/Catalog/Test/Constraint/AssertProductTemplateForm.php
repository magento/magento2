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

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductTemplateForm
 * Checking data from Product Template form with data fixture
 */
class AssertProductTemplateForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that after save a product template on edit product set page displays:
     * 1. Correct product template name in Attribute set name field passed from fixture
     * 2. Created Product Attribute (if was added)
     *
     * @param CatalogProductSetIndex $productSet
     * @param CatalogProductSetEdit $productSetEdit
     * @param CatalogAttributeSet $attributeSet
     * @param CatalogProductAttribute $productAttribute
     * @return void
     */
    public function processAssert
    (
        CatalogProductSetIndex $productSet,
        CatalogProductSetEdit $productSetEdit,
        CatalogAttributeSet $attributeSet,
        CatalogProductAttribute $productAttribute = null
    ) {
        $filterAttribute = [
            'set_name' => $attributeSet->getAttributeSetName(),
        ];
        $productSet->open();
        $productSet->getGrid()->searchAndOpen($filterAttribute);
        \PHPUnit_Framework_Assert::assertEquals(
            $filterAttribute['set_name'],
            $productSetEdit->getAttributeSetEditBlock()->getAttributeSetName(),
            'Attribute Set not found'
            . "\nExpected: " . $filterAttribute['set_name']
            . "\nActual: " . $productSetEdit->getAttributeSetEditBlock()->getAttributeSetName()
        );
        if ($productAttribute !== null) {
            $attributeLabel = $productAttribute->getFrontendLabel();
            \PHPUnit_Framework_Assert::assertTrue(
                $productSetEdit->getAttributeSetEditBlock()->checkProductAttribute($attributeLabel),
                "Product Attribute is absent on Product Template Groups"
            );
        }
    }

    /**
     * Text matches the data from a form with data from fixture
     *
     * @return string
     */
    public function toString()
    {
        return 'Data from the Product Template form matched with fixture';
    }
}
