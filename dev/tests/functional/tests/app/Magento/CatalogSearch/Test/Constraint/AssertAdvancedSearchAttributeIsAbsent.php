<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\CatalogSearch\Test\Page\AdvancedSearch;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\Cli\Indexer;

/**
 * Assert that created custom product attribute is absent in the advanced search form on the frontend.
 */
class AssertAdvancedSearchAttributeIsAbsent extends AbstractConstraint
{
    /**
     * Assert that created custom product attribute is absent in the advanced search form on the frontend.
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
        $formLabels = $advancedSearch->getForm()->getFormLabels();
        $label = $attribute->hasData('manage_frontend_label')
            ? $attribute->getManageFrontendLabel()
            : $attribute->getFrontendLabel();
        \PHPUnit_Framework_Assert::assertFalse(
            in_array($label, $formLabels),
            'Created custom product attribute is present in advanced search form on frontend but must be absent.'
        );
    }

    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Created custom product attribute is absent in advanced search form on frontend.';
    }
}
