<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Newsletter\Test\Constraint;

use Magento\Newsletter\Test\Fixture\Template;
use Magento\Newsletter\Test\Page\Adminhtml\TemplateIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertNewsletterInGrid
 *
 * @package Magento\Newsletter\Test\Constraint
 */
class AssertNewsletterInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     *  Assert that newsletter template is present in grid
     *
     * @param TemplateIndex $templateIndex
     * @param Template $template
     * @return void
     */
    public function processAssert(
        TemplateIndex $templateIndex,
        Template $template
    ) {
        $templateIndex->open();
        $filter = ['code' => $template->getCode()];
        \PHPUnit_Framework_Assert::assertTrue(
            $templateIndex->getNewsletterTemplateGrid()->isRowVisible($filter),
            'Newsletter \'' . $template->getCode() . '\'is absent in newsletter template grid.'
        );
    }

    /**
     * Success assert of newsletter template in grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'Newsletter template is present in grid.';
    }
}
