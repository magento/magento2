<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Look on the scope of product attribute in the grid.
 */
class AssertProductAttributeIsGlobal extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Look on the scope of product attribute in the grid.
     *
     * @param CatalogProductAttributeIndex $catalogProductAttributeIndex
     * @param CatalogProductAttribute $attribute
     * @return void
     */
    public function processAssert(
        CatalogProductAttributeIndex $catalogProductAttributeIndex,
        CatalogProductAttribute $attribute
    ) {
        $filter = ['frontend_label' => $attribute->getFrontendLabel(), 'is_global' => $attribute->getIsGlobal()];

        \PHPUnit_Framework_Assert::assertTrue(
            $catalogProductAttributeIndex->open()->getGrid()->isRowVisible($filter),
            'Attribute is not global.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is global.';
    }
}
