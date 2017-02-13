<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\CatalogSearch\Test\Page\AdvancedSearch;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\Cli\Indexer;

/**
 * Check whether attribute is absent in the advanced search form on the frontend.
 */
class AssertAdvancedSearchAttributeIsAbsent extends AbstractConstraint
{
    /**
     * Check that the attribute is absent in the advanced search form on the frontend.
     *
     * @param CatalogProductAttribute $attribute
     * @param AdvancedSearch $advancedSearch
     * @param Indexer $cli
     * @return void
     */
    public function processAssert(CatalogProductAttribute $attribute, AdvancedSearch $advancedSearch, Indexer $cli)
    {
        $cli->reindex();
        $advancedSearch->open();
        $formLabels = $advancedSearch->getForm()->getFormlabels();
        $label = $attribute->hasData('manage_frontend_label')
            ? $attribute->getManageFrontendLabel()
            : $attribute->getFrontendLabel();
        \PHPUnit_Framework_Assert::assertFalse(
            in_array($label, $formLabels),
            'Attribute is present on advanced search form.'
        );
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is absent on advanced search form.';
    }
}
