<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_block = $objectManager->create('Magento\Catalog\Block\Product\View');
        $this->_product = $objectManager->create('Magento\Catalog\Model\Product');
        $this->_product->load(1);
        $objectManager->get('Magento\Framework\Registry')->unregister('product');
        $objectManager->get('Magento\Framework\Registry')->register('product', $this->_product);
    }

    public function testSetLayout()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = $objectManager->get('Magento\Framework\View\LayoutInterface');
        /** @var $pageConfig \Magento\Framework\View\Page\Config */
        $pageConfig = $objectManager->get('Magento\Framework\View\Page\Config');

        $layout->addBlock($this->_block);

        $this->assertNotEmpty($pageConfig->getTitle());
        $this->assertEquals($this->_product->getMetaTitle(), $pageConfig->getTitle());
        $this->assertEquals($this->_product->getMetaKeyword(), $pageConfig->getKeywords());
        $this->assertEquals($this->_product->getMetaDescription(), $pageConfig->getDescription());
    }

    public function testGetProduct()
    {
        $this->assertNotEmpty($this->_block->getProduct()->getId());
        $this->assertEquals($this->_product->getId(), $this->_block->getProduct()->getId());

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->unregister('product');
        $this->_block->setProductId(1);
        $this->assertEquals($this->_product->getId(), $this->_block->getProduct()->getId());
    }

    public function testCanEmailToFriend()
    {
        $this->assertFalse($this->_block->canEmailToFriend());
    }

    public function testGetAddToCartUrl()
    {
        $url = $this->_block->getAddToCartUrl($this->_product);
        $this->assertStringMatchesFormat('%scheckout/cart/add/%sproduct/1/', $url);
    }

    public function testGetJsonConfig()
    {
        $config = (array)json_decode($this->_block->getJsonConfig());
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('productId', $config);
        $this->assertEquals(1, $config['productId']);
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
