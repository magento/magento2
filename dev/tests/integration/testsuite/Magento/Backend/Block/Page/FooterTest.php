<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Page;

/**
 * Test \Magento\Backend\Block\Page\Footer
 * @magentoAppArea adminhtml
 */
class FooterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Product Version Value
     */
    const TEST_PRODUCT_VERSION = '222.333.444';

    /**
     * @var \Magento\Backend\Block\Page\Footer
     */
    protected $block;

    protected function setUp()
    {
        parent::setUp();
        $productMetadataMock =  $this->getMockBuilder('Magento\Framework\App\ProductMetadata')
            ->setMethods(['getVersion'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn($this::TEST_PRODUCT_VERSION);
        $this->block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\Page\Footer',
            '',
            ['productMetadata' => $productMetadataMock]
        );
    }

    public function testToHtml()
    {
        $footerContent = $this->block->toHtml();
        $this->assertContains('ver. ' . $this::TEST_PRODUCT_VERSION, $footerContent, 'No or wrong product version.');
    }
}
