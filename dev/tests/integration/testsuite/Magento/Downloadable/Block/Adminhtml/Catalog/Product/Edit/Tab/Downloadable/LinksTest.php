<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

class LinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppArea adminhtml
     */
    public function testGetUploadButtonsHtml()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links'
        );
        self::performUploadButtonTest($block);
    }

    /**
     * Reuse code for testing getUploadButtonHtml()
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     */
    public static function performUploadButtonTest(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Framework\View\Layout');
        $layout->addBlock($block, 'links');
        $expected = uniqid();
        $text = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Framework\View\Element\Text',
            '',
            ['data' => ['text' => $expected]]
        );
        $block->unsetChild('upload_button');
        $layout->addBlock($text, 'upload_button', 'links');
        self::assertEquals($expected, $block->getUploadButtonHtml());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     */
    public function testGetLinkData()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'product',
            new \Magento\Framework\Object(['type_id' => 'simple'])
        );
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links'
        );
        $this->assertEmpty($block->getLinkData());
    }

    /**
     * Get Links Title for simple/virtual/downloadable product
     *
     * @magentoConfigFixture current_store catalog/downloadable/links_title Links Title Test
     * @magentoAppIsolation enabled
     * @dataProvider productLinksTitleDataProvider
     *
     * @magentoAppArea adminhtml
     * @param string $productType
     * @param string $linksTitle
     * @param string $expectedResult
     */
    public function testGetLinksTitle($productType, $linksTitle, $expectedResult)
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'product',
            new \Magento\Framework\Object(['type_id' => $productType, 'id' => '1', 'links_title' => $linksTitle])
        );
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links'
        );
        $this->assertEquals($expectedResult, $block->getLinksTitle());
    }

    /**
     * Data Provider with product types
     *
     * @return array
     */
    public function productLinksTitleDataProvider()
    {
        return [
            ['simple', null, 'Links Title Test'],
            ['simple', 'Links Title', 'Links Title Test'],
            ['virtual', null, 'Links Title Test'],
            ['virtual', 'Links Title', 'Links Title Test'],
            ['downloadable', null, null],
            ['downloadable', 'Links Title', 'Links Title']
        ];
    }
}
