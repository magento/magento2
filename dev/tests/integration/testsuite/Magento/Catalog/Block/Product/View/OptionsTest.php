<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    protected $block;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->productRepository = $this->objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');

        try {
            $this->product = $this->productRepository->get('simple');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->product = $this->productRepository->get('simple_dropdown_option');
        }

        $this->objectManager->get('Magento\Framework\Registry')->unregister('current_product');
        $this->objectManager->get('Magento\Framework\Registry')->register('current_product', $this->product);

        $this->block = $this->objectManager->get(
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
        $this->assertSame($this->product, $this->block->getProduct());

        $product = $this->objectManager->create(
            'Magento\Catalog\Model\Product'
        );
        $this->block->setProduct($product);
        $this->assertSame($product, $this->block->getProduct());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetGroupOfOption()
    {
        $this->assertEquals('default', $this->block->getGroupOfOption('test'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetOptions()
    {
        $options = $this->block->getOptions();
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
        $this->assertTrue($this->block->hasOptions());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_dropdown_option.php
     */
    public function testGetJsonConfig()
    {
        $config = json_decode($this->block->getJsonConfig(), true);
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
