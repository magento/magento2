<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Cms\Controller\Page.
 */
namespace Magento\Cms\Controller;

use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;

class PageTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                \Magento\Cms\Model\Page\CustomLayoutManagerInterface::class =>
                    \Magento\TestFramework\Cms\Model\CustomLayoutManager::class
            ]
        ]);
        parent::setUp();
    }

    public function testViewAction()
    {
        $this->dispatch('/enable-cookies');
        $this->assertStringContainsString('What are Cookies?', $this->getResponse()->getBody());
    }

    public function testViewRedirectWithTrailingSlash()
    {
        $this->dispatch('/enable-cookies/');
        $code = $this->getResponse()->getStatusCode();
        $location = $this->getResponse()->getHeader('Location')->getFieldValue();

        $this->assertEquals(301, $code, 'Invalid response code');
        $this->assertStringEndsWith('/enable-cookies', $location, 'Invalid location header');
    }

    /**
     * Test \Magento\Cms\Block\Page::_addBreadcrumbs
     */
    public function testAddBreadcrumbs()
    {
        $this->dispatch('/enable-cookies');
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        $breadcrumbsBlock = $layout->getBlock('breadcrumbs');
        $this->assertStringContainsString($breadcrumbsBlock->toHtml(), $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture cmsPageWithSystemRouteFixture
     */
    public function testCreatePageWithSameModuleName()
    {
        $this->dispatch('/shipping');
        $content = $this->getResponse()->getBody();
        $this->assertStringContainsString('Shipping Test Page', $content);
    }

    public static function cmsPageWithSystemRouteFixture()
    {
        /** @var $page \Magento\Cms\Model\Page */
        $page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Page::class);
        $page->setTitle('Test title')
            ->setIdentifier('shipping')
            ->setStores([0])
            ->setIsActive(1)
            ->setContent('<h1>Shipping Test Page</h1>')
            ->setPageLayout('1column')
            ->save();
    }

    /**
     * Check that custom handles are applied when rendering a page.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Cms/_files/pages_with_layout_xml.php
     */
    public function testCustomHandles(): void
    {
        /** @var GetPageByIdentifierInterface $pageFinder */
        $pageFinder = Bootstrap::getObjectManager()->get(GetPageByIdentifierInterface::class);
        $page = $pageFinder->execute('test_custom_layout_page_3', 0);
        $this->dispatch('/cms/page/view/page_id/' .$page->getId());
        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $handles = $layout->getUpdate()->getHandles();
        $this->assertContains('cms_page_view_selectable_test_custom_layout_page_3_test_selected', $handles);
    }

    /**
     * Check home page custom handle is applied when rendering a page.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Cms/_files/home_with_custom_handle.php
     */
    public function testHomePageCustomHandles(): void
    {
        $this->dispatch('/');
        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $handles = $layout->getUpdate()->getHandles();
        $this->assertContains('cms_page_view_selectable_home_page_custom_layout', $handles);
    }
}
