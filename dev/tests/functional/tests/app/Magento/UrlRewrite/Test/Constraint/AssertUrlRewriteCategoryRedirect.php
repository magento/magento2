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

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\Catalog\Test\Fixture\CatalogCategory;

/**
 * Class AssertUrlRewriteCategoryRedirect
 * Assert check URL rewrite category redirect
 */
class AssertUrlRewriteCategoryRedirect extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert check URL rewrite category redirect
     *
     * @param UrlRewrite $urlRewrite
     * @param CatalogCategory $category
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        UrlRewrite $urlRewrite,
        CatalogCategory $category,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $urlRewrite->getRequestPath());
        $url = $urlRewrite->getRedirectType() == 'No'
            ? $urlRewrite->getRequestPath()
            : $category->getUrlKey() . '.html';

        \PHPUnit_Framework_Assert::assertEquals(
            $browser->getUrl(),
            $_ENV['app_frontend_url'] . $url,
            'URL rewrite category redirect false.'
            . "\nExpected: " . $_ENV['app_frontend_url'] . $url
            . "\nActual: " . $browser->getUrl()
        );

    }

    /**
     * URL rewrite category redirect success
     *
     * @return string
     */
    public function toString()
    {
        return 'URL rewrite category redirect success.';
    }
}
