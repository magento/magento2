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

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Class AssertAbsenceProductAttributeInGrid
 * Checks that product attribute cannot be found by attribute code
 */
class AssertProductAttributeAbsenceInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that after deleted product attribute cannot be found by attribute code.
     *
     * @param CatalogProductAttributeIndex $attributeIndex
     * @param CatalogProductAttribute $attribute
     * @return void
     */
    public function processAssert(
        CatalogProductAttributeIndex $attributeIndex,
        CatalogProductAttribute $attribute
    ) {
        $filter = [
            'attribute_code' => $attribute->getAttributeCode(),
        ];

        $attributeIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $attributeIndex->getGrid()->isRowVisible($filter),
            'Attribute \'' . $attribute->getFrontendLabel() . '\' is present in Attribute grid.'
        );
    }

    /**
     * Text absent Product Attribute in Attribute Grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Attribute is absent in Attribute grid.';
    }
}
