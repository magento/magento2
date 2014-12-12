<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Test class for \Magento\Cms\Controller\Page.
 */
namespace Magento\Cms\Controller;

class PageTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testViewAction()
    {
        $this->dispatch('/enable-cookies/');
        $this->assertContains('What are Cookies?', $this->getResponse()->getBody());
    }

    /**
     * Test \Magento\Cms\Block\Page::_addBreadcrumbs
     */
    public function testAddBreadcrumbs()
    {
        $this->dispatch('/enable-cookies/');
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $breadcrumbsBlock = $layout->getBlock('breadcrumbs');
        $this->assertContains($breadcrumbsBlock->toHtml(), $this->getResponse()->getBody());
    }
}
