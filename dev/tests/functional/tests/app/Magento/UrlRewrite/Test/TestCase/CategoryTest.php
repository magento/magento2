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

namespace Magento\UrlRewrite\Test\TestCase;

use Mtf\Factory\Factory;
use Magento\UrlRewrite\Test\Fixture\UrlRewriteCategory;
use Mtf\TestCase\Injectable;

/**
 * Class UrlRewriteTest
 * Category URL rewrite creation test
 */
class CategoryTest extends Injectable
{
    /**
     * Adding permanent redirect for category
     *
     * @param UrlRewriteCategory $urlRewriteCategory
     * @return void
     * @ZephyrId MAGETWO-12407
     */
    public function test(\Magento\UrlRewrite\Test\Fixture\UrlRewriteCategory $urlRewriteCategory)
    {
        $urlRewriteCategory->switchData('category_with_permanent_redirect');
        //Pages & Blocks
        $urlRewriteIndexPage = Factory::getPageFactory()->getAdminUrlRewriteIndex();
        $pageActionsBlock = $urlRewriteIndexPage->getPageActionsBlock();
        $urlRewriteEditPage = Factory::getPageFactory()->getAdminUrlRewriteEdit();
        $categoryTreeBlock = $urlRewriteEditPage->getTreeBlock();
        $urlRewriteInfoForm = $urlRewriteEditPage->getFormBlock();
        $typeSelectorBlock = $urlRewriteEditPage->getUrlRewriteTypeSelectorBlock();

        //Steps
        Factory::getApp()->magentoBackendLoginUser();
        $urlRewriteIndexPage->open();
        $pageActionsBlock->addNew();
        $typeSelectorBlock->selectType($urlRewriteCategory->getUrlRewriteType());
        $categoryTreeBlock->selectCategory($urlRewriteCategory->getCategoryName());
        $urlRewriteInfoForm->fill($urlRewriteCategory);
        $urlRewriteEditPage->getPageMainActions()->save();
        $this->assertContains(
            'The URL Rewrite has been saved.',
            $urlRewriteIndexPage->getMessagesBlock()->getSuccessMessages()
        );

        $this->assertUrlRewrite(
            $_ENV['app_frontend_url'] . $urlRewriteCategory->getRewrittenRequestPath(),
            $_ENV['app_frontend_url'] . $urlRewriteCategory->getOriginalRequestPath()
        );
    }

    /**
     * Assert that request URL redirects to target URL
     *
     * @param string $requestUrl
     * @param string $targetUrl
     * @param string $message
     * @return void
     */
    protected function assertUrlRewrite($requestUrl, $targetUrl, $message = '')
    {
        $browser = Factory::getClientBrowser();
        $browser->open($requestUrl);
        $this->assertStringStartsWith($targetUrl, $browser->getUrl(), $message);
    }
}
