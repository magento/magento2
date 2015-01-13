<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductTemplateNotInGrid
 * Assert that Product Template absence on grid
 */
class AssertProductTemplateNotInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that product template is not displayed in Product Templates grid
     *
     * @param CatalogProductSetIndex $productSetPage
     * @param CatalogAttributeSet $productTemplate
     * @return void
     */
    public function processAssert(CatalogProductSetIndex $productSetPage, CatalogAttributeSet $productTemplate)
    {
        $filterAttributeSet = [
            'set_name' => $productTemplate->getAttributeSetName(),
        ];

        $productSetPage->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $productSetPage->getGrid()->isRowVisible($filterAttributeSet),
            'Attribute Set with name "' . $filterAttributeSet['set_name'] . '" is present in Product Template grid.'
        );
    }

    /**
     * Text absent new product template in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Product template is absent in Product Templates grid';
    }
}
