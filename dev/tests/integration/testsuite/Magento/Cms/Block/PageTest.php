<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block;

class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testGetPage()
    {
        $page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Cms\Model\Page::class);
        $page->load('page100', 'identifier');
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        $pageBlock = $layout->createBlock(\Magento\Cms\Block\Page::class);
        $pageBlock->setData('page', $page);
        $pageBlock->toHtml();
        $this->assertEquals($page, $pageBlock->getPage());
    }
}
