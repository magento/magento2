<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller;

use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Model\Page\CustomLayoutManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Cms\Model\CustomLayoutManager;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for \Magento\Cms\Controller\Page\View class.
 */
class PageTest extends AbstractController
{
    /**
     * @var GetPageByIdentifierInterface
     */
    private $pageRetriever;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->_objectManager->configure([
            'preferences' => [
                CustomLayoutManagerInterface::class => CustomLayoutManager::class,
            ],
        ]);
        $this->pageRetriever = $this->_objectManager->get(GetPageByIdentifierInterface::class);
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
        $layout = $this->_objectManager->get(LayoutInterface::class);
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
        $page = $this->pageRetriever->execute('test_custom_layout_page_3', 0);
        $this->dispatch('/cms/page/view/page_id/' . $page->getId());
        /** @var LayoutInterface $layout */
        $layout = $this->_objectManager->get(LayoutInterface::class);
        $handles = $layout->getUpdate()->getHandles();
        $this->assertContains('cms_page_view_selectable_test_custom_layout_page_3_test_selected',$handles);
    }

    /**
     * Tests page renders even with unavailable custom page layout.
     *
     * @magentoDataFixture Magento/Cms/Fixtures/page_list.php
     * @dataProvider pageLayoutDataProvider
     * @param string $pageIdentifier
     * @return void
     */
    public function testPageWithCustomLayout(string $pageIdentifier): void
    {
        $page = $this->pageRetriever->execute($pageIdentifier, 0);
        $this->dispatch('/cms/page/view/page_id/' . $page->getId());
        $this->assertStringContainsString(
            '<main id="maincontent" class="page-main">',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @return array
     */
    public function pageLayoutDataProvider(): array
    {
        return [
            'Page with 1column layout' => ['page-with-1column-layout'],
            'Page with unavailable layout' => ['page-with-unavailable-layout'],
        ];
    }
}
