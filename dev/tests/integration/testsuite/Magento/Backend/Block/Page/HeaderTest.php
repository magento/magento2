<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Page;

/**
 * Test \Magento\Backend\Block\Page\Header
 * @magentoAppArea adminhtml
 */
class HeaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Page\Header
     */
    protected $_block;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Backend\Block\Page\Header::class
        );
    }

    public function testGetHomeLink()
    {
        $expected = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Backend\Helper\Data::class
        )->getHomePageUrl();
        $this->assertEquals($expected, $this->_block->getHomeLink());
    }
}
