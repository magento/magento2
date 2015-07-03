<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item;

/**
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_and_image.php
 */
class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Cart\Item\Renderer
     */
    protected $_block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock('Magento\Checkout\Block\Cart\Item\Renderer');

        /** @var $session \Magento\Checkout\Model\Session  */
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Checkout\Model\Session');

        $item = $this->_getQuoteItemIdByProductId($session->getQuote(), 1);
        $this->assertNotNull($item, 'Cannot get quote item for simple product');

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        $item->setProduct($product);
        $this->_block->setItem($item);
    }

    protected function tearDown()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $objectManager->removeSharedInstance('Magento\Checkout\Model\Session');
        $objectManager->removeSharedInstance('Magento\Checkout\Model\Session\Storage');

        unset($_SESSION);
    }

    public function testThumbnail()
    {
        $size = $this->_block->getThumbnailSize();
        $sidebarSize = $this->_block->getThumbnailSidebarSize();
        $this->assertGreaterThan(1, $size);
        $this->assertGreaterThan(1, $sidebarSize);
        $this->assertContains('/' . $size, $this->_block->getProductThumbnailUrl());
        $this->assertContains('/' . $sidebarSize, $this->_block->getProductThumbnailSidebarUrl());
        $this->assertStringEndsWith('magento_image.jpg', $this->_block->getProductThumbnailUrl());
        $this->assertStringEndsWith('magento_image.jpg', $this->_block->getProductThumbnailSidebarUrl());
    }

    /**
     * Gets \Magento\Quote\Model\Quote\Item from \Magento\Quote\Model\Quote by product id
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param $productId
     * @return \Magento\Quote\Model\Quote\Item|null
     */
    private function _getQuoteItemIdByProductId($quote, $productId)
    {
        /** @var $quoteItems \Magento\Quote\Model\Quote\Item[] */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($productId == $quoteItem->getProductId()) {
                return $quoteItem;
            }
        }
        return null;
    }
}
