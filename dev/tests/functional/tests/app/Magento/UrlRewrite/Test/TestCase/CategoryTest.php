<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\UrlRewrite\Test\Fixture\UrlRewriteCategory;
use Mtf\Factory\Factory;
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
