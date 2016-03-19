<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\View;

/**
 * Test class for \Magento\Catalog\Block\Product\View\Options.
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\View\Options
     */
    protected $_block;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    protected function setUp()
    {
        $this->_product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->_product->load(1);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->unregister('current_product');
        $objectManager->get('Magento\Framework\Registry')->register('current_product', $this->_product);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Catalog\Block\Product\View\Options'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSetGetProduct()
    {
        $this->assertSame($this->_product, $this->_block->getProduct());

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->_block->setProduct($product);
        $this->assertSame($product, $this->_block->getProduct());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetGroupOfOption()
    {
        $this->assertEquals('default', $this->_block->getGroupOfOption('test'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetOptions()
    {
        $options = $this->_block->getOptions();
        $this->assertNotEmpty($options);
        foreach ($options as $option) {
            $this->assertInstanceOf('Magento\Catalog\Model\Product\Option', $option);
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testHasOptions()
    {
        $this->assertTrue($this->_block->hasOptions());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_dropdown_option.php
     */
    public function testGetJsonConfig()
    {
        $config = json_decode($this->_block->getJsonConfig(), true);
        $configValues = array_values($config);
        $this->assertEquals($this->getExpectedJsonConfig(), array_values($configValues[0]));
    }

    /**
     * Expected data for testGetJsonConfig
     *
     * @return array
     */
    private function getExpectedJsonConfig()
    {
        return [
            0 =>
                ['prices' =>
                    ['oldPrice' =>
                        ['amount' => 10, 'adjustments' => []],
                        'basePrice' => ['amount' => 10],
                        'finalPrice' => ['amount' => 10]
                    ],
                    'type' => 'fixed',
                    'name' => 'drop_down option 1',
                ],
            1 =>
                ['prices' =>
                    ['oldPrice' =>
                        ['amount' => 40, 'adjustments' => []],
                        'basePrice' => ['amount' => 40],
                        'finalPrice' => ['amount' => 40],
                    ],
                    'type' => 'percent',
                    'name' => 'drop_down option 2',
                ],
        ];
    }
}
