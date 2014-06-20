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
use Magento\Cms\Test\Fixture\CmsPage;

/**
 * Class AssertUrlRewriteCmsPageRedirect
 * Assert that created CMS Page URL Redirect lead to appropriate page in frontend
 */
class AssertUrlRewriteCmsPageRedirect extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * URL for CMS Page
     *
     * @var string
     */
    protected $url = 'cms/page/view/page_id/';

    /**
     * Assert that created CMS Page URL Redirect lead to appropriate page in frontend
     *
     * @param UrlRewrite $urlRewrite
     * @param CmsPage $cmsPage
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        UrlRewrite $urlRewrite,
        CmsPage $cmsPage,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $urlRewrite->getRequestPath());
        $url = $urlRewrite->getOptions() == 'No'
            ? $urlRewrite->getRequestPath()
            : $this->url . $cmsPage->getPageId();

        \PHPUnit_Framework_Assert::assertEquals(
            $browser->getUrl(),
            $_ENV['app_frontend_url'] . $url,
            'URL rewrite CMS Page redirect false.'
            . "\nExpected: " . $_ENV['app_frontend_url'] . $url
            . "\nActual: " . $browser->getUrl()
        );
    }

    /**
     * URL Redirect lead to appropriate page in frontend
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Redirect lead to appropriate page in frontend.';
    }
}
