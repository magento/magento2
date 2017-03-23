<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\CatalogSearch\Test\Page\AdvancedSearch;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check whether attribute is displayed in the advanced search form on the frontend.
 */
class AssertProductAttributeDisplayingOnSearchForm extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Check whether attribute is displayed in the advanced search form on the frontend.
     *
     * @param CatalogProductAttribute $attribute
     * @param AdvancedSearch $advancedSearch
     * @return void
     */
    public function processAssert(CatalogProductAttribute $attribute, AdvancedSearch $advancedSearch)
    {
        $advancedSearch->open();
        $formLabels = $advancedSearch->getForm()->getFormlabels();
        $label = $attribute->hasData('manage_frontend_label')
            ? $attribute->getManageFrontendLabel()
            : $attribute->getFrontendLabel();
        \PHPUnit_Framework_Assert::assertTrue(
            in_array($label, $formLabels),
            'Attribute is absent on advanced search form.'
        );
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is present on advanced search form.';
    }
}
