<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Block\Checkout;

/**
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class OverviewTest extends \PHPUnit\Framework\DOMTestCase
{
    /**
     * @var \Magento\Multishipping\Block\Checkout\Overview
     */
    protected $_block;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_block = $this->_objectManager->get(\Magento\Framework\View\LayoutInterface::class)
            ->createBlock(
                 \Magento\Multishipping\Block\Checkout\Overview::class,
                'checkout_overview',
                [
                    'data' => [
                        'renderer_template' => 'Magento_Multishipping::checkout/item/default.phtml',
                        'row_renderer_template' => 'Magento_Multishipping::checkout/overview/item.phtml',
                    ],
                ]
            );

        $this->_block->addChild('renderer.list', \Magento\Framework\View\Element\RendererList::class);
        $this->_block->getChildBlock(
            'renderer.list'
        )->addChild(
            'default', \Magento\Checkout\Block\Cart\Item\Renderer::class,
            ['template' => 'cart/item/default.phtml']
        );
    }

    public function testGetRowItemHtml()
    {
        /** @var $item \Magento\Quote\Model\Quote\Item */
        $item = $this->_objectManager->create(\Magento\Quote\Model\Quote\Item::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load(1);
        $item->setProduct($product);
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->_objectManager->create(\Magento\Quote\Model\Quote::class);
        $item->setQuote($quote);
        // assure that default renderer was obtained
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[contains(@class,"product") and contains(@class,"name")]/a',
                $this->_block->getRowItemHtml($item)
            )
        );
    }
}
