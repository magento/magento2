<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Page\AdvancedSearch;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert advanced attribute is present(or absent) in Advanced Search Page.
 */
class AssertSearchAttributeTest extends AbstractConstraint
{
    /**
     * Assert advanced attribute is present(or absent) in Advanced Search Page.
     *
     * @param AdvancedSearch $advancedSearch
     * @param string $attributeForSearch
     * @param bool $isAbsent
     * @return void
     */
    public function processAssert(
        AdvancedSearch $advancedSearch,
        $attributeForSearch,
        $isAbsent = true
    ) {
        $advancedSearch->open();
        $availableAttributes = $advancedSearch->getForm()->getFormLabels();
        if ($isAbsent) {
            \PHPUnit_Framework_Assert::assertTrue(
                (false !== array_search($attributeForSearch, $availableAttributes)),
                'Attribute ' . $attributeForSearch . 'was not found in Advanced Search Page.'
            );
        } else {
            \PHPUnit_Framework_Assert::assertTrue(
                (false == array_search($attributeForSearch, $availableAttributes)),
                'Attribute ' . $attributeForSearch . ' was found in Advanced Search Page.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute was found in Advanced Search Page.';
    }
}
