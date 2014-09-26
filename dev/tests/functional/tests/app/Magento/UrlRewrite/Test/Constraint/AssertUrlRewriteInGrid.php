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

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteInGrid
 * Assert that url rewrite category in grid
 */
class AssertUrlRewriteInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that url rewrite category in grid
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function processAssert(UrlRewriteIndex $urlRewriteIndex, UrlRewrite $urlRewrite)
    {
        $urlRewriteIndex->open();
        $filter = ['request_path' => $urlRewrite->getRequestPath()];
        \PHPUnit_Framework_Assert::assertTrue(
            $urlRewriteIndex->getUrlRewriteGrid()->isRowVisible($filter),
            'URL Rewrite with request path \'' . $urlRewrite->getRequestPath() . '\' is absent in grid.'
        );
    }

    /**
     * URL rewrite category present in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite is present in grid.';
    }
}
