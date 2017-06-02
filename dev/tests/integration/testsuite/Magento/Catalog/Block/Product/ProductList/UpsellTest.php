<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList;

/**
 * Test class for \Magento\Catalog\Block\Product\List\Upsell.
 *
 * @magentoDataFixture Magento/Catalog/_files/products_upsell.php
 */
class UpsellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Upsell
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    protected $upsellProduct;

    protected function setUp()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $this->upsellProduct = $productRepository->get('simple');
        $this->product = $productRepository->get('simple_with_upsell');
        $objectManager->get(\Magento\Framework\Registry::class)->register('product', $this->product);

        $this->block = $objectManager->get(\Magento\Framework\View\LayoutInterface::class)
            ->createBlock(\Magento\Catalog\Block\Product\ProductList\Upsell::class);

        $this->block->setLayout($objectManager->get(\Magento\Framework\View\LayoutInterface::class));
        $this->block->setTemplate('Magento_Catalog::product/list/items.phtml');
        $this->block->setType('upsell');
        $this->block->addChild('addto', \Magento\Catalog\Block\Product\ProductList\Item\Container::class);
        $this->block->getChildBlock(
            'addto'
        )->addChild(
            'compare',
            \Magento\Catalog\Block\Product\ProductList\Item\AddTo\Compare::class,
            ['template' => 'Magento_Catalog::product/list/addto/compare.phtml']
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAll()
    {
        $html = $this->block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertContains('Simple Up Sell', $html);
        $this->assertCount(1, $this->block->getItems());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetIdentities()
    {
        $expectedTags = ['cat_p_' . $this->upsellProduct->getId(), 'cat_p'];
        $tags = $this->block->getIdentities();
        $this->assertEquals($expectedTags, $tags);
    }
}
