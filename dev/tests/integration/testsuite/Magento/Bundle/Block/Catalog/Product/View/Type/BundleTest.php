<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Catalog\Product\View\Type;

/**
 * @magentoDataFixture Magento/Bundle/_files/product.php
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class BundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
     */
    private $block;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    private $product;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->product = $this->productRepository->get('bundle-product', false, null, true);
        $this->product->setPriceType(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC)->save();
        $this->objectManager->get(\Magento\Framework\Registry::class)->unregister('product');
        $this->objectManager->get(\Magento\Framework\Registry::class)->register('product', $this->product);

        $this->block = $this->objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle::class
        );
    }

    /**
     * Test for method \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle::getJsonConfig
     *
     * @return void
     */
    public function testGetJsonConfig()
    {
        $option = $this->productRepository->get('simple');
        $option->setSpecialPrice(5)
            ->save();
        $config = json_decode($this->block->getJsonConfig(), true);
        $options = current($config['options']);
        $selection = current($options['selections']);
        $this->assertEquals(10, $selection['prices']['oldPrice']['amount']);
        $this->assertEquals(5, $selection['prices']['basePrice']['amount']);
        $this->assertEquals(5, $selection['prices']['finalPrice']['amount']);
    }
}
