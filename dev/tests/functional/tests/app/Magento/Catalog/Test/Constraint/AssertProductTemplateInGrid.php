<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductTemplateInGrid
 * Checks present product template in Product Templates grid
 */
class AssertProductTemplateInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that new product template displays in Product Templates grid
     *
     * @param CatalogProductSetIndex $productSetPage
     * @param CatalogAttributeSet $attributeSet
     * @return void
     */
    public function processAssert(CatalogProductSetIndex $productSetPage, CatalogAttributeSet $attributeSet)
    {
        $filterAttributeSet = [
            'set_name' => $attributeSet->getAttributeSetName(),
        ];

        $productSetPage->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $productSetPage->getGrid()->isRowVisible($filterAttributeSet),
            'Attribute Set \'' . $filterAttributeSet['set_name'] . '\' is absent in Product Template grid.'
        );
    }

    /**
     * Text present new product template in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Product template is present in Product Templates grid';
    }
}
