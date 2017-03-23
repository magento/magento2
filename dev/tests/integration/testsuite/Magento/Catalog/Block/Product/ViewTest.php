<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * Test class for \Magento\Catalog\Block\Product\View.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\View
     */
    protected $_block;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_block = $objectManager->create(\Magento\Catalog\Block\Product\View::class);

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->_product = $productRepository->get('simple');

        $objectManager->get(\Magento\Framework\Registry::class)->unregister('product');
        $objectManager->get(\Magento\Framework\Registry::class)->register('product', $this->_product);
    }

    public function testSetLayout()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = $objectManager->get(\Magento\Framework\View\LayoutInterface::class);
        /** @var $pageConfig \Magento\Framework\View\Page\Config */
        $pageConfig = $objectManager->get(\Magento\Framework\View\Page\Config::class);

        $layout->createBlock(\Magento\Catalog\Block\Product\View::class);

        $this->assertNotEmpty($pageConfig->getTitle()->get());
        $this->assertEquals($this->_product->getMetaTitle(), $pageConfig->getTitle()->get());
        $this->assertEquals($this->_product->getMetaKeyword(), $pageConfig->getKeywords());
        $this->assertEquals($this->_product->getMetaDescription(), $pageConfig->getDescription());
    }

    public function testGetProduct()
    {
        $this->assertNotEmpty($this->_block->getProduct()->getId());
        $this->assertEquals($this->_product->getId(), $this->_block->getProduct()->getId());

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('product');
        $this->_block->setProductId($this->_product->getId());
        $this->assertEquals($this->_product->getId(), $this->_block->getProduct()->getId());
    }

    public function testCanEmailToFriend()
    {
        $this->assertFalse($this->_block->canEmailToFriend());
    }

    public function testGetAddToCartUrl()
    {
        $url = $this->_block->getAddToCartUrl($this->_product);
        $this->assertStringMatchesFormat('%scheckout/cart/add/%sproduct/' . $this->_product->getId() . '/', $url);
    }

    public function testGetJsonConfig()
    {
        $config = (array)json_decode($this->_block->getJsonConfig());
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('productId', $config);
        $this->assertEquals($this->_product->getId(), $config['productId']);
    }

    public function testHasOptions()
    {
        $this->assertTrue($this->_block->hasOptions());
    }

    public function testHasRequiredOptions()
    {
        $this->assertTrue($this->_block->hasRequiredOptions());
    }

    public function testStartBundleCustomization()
    {
        $this->markTestSkipped("Functionality not implemented in Magento 1.x. Implemented in Magento 2");
        $this->assertFalse($this->_block->startBundleCustomization());
    }
}
