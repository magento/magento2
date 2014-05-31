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
