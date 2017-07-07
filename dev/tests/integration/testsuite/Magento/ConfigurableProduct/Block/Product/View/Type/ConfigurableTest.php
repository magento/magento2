<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Product\View\Type;

/**
 * Test class for \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable.
 *
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
     */
    protected $_block;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    protected function setUp()
    {
        $this->_product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $this->_product->load(1);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable::class
        );
        $this->_block->setProduct($this->_product);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetAllowAttributes()
    {
        $attributes = $this->_block->getAllowAttributes();
        $this->assertInstanceOf(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection::class,
            $attributes
        );
        $this->assertGreaterThanOrEqual(1, $attributes->getSize());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testHasOptions()
    {
        $this->assertTrue($this->_block->hasOptions());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetAllowProducts()
    {
        $products = $this->_block->getAllowProducts();
        $this->assertGreaterThanOrEqual(2, count($products));
        foreach ($products as $product) {
            $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $product);
        }
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetJsonConfig()
    {
        $config = json_decode($this->_block->getJsonConfig(), true);
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('productId', $config);
        $this->assertEquals(1, $config['productId']);
        $this->assertArrayHasKey('attributes', $config);
        $this->assertArrayHasKey('template', $config);
        $this->assertArrayHasKey('prices', $config);
        $this->assertArrayHasKey('basePrice', $config['prices']);
    }
}
