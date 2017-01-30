<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList;

/**
 * Test class for \Magento\Catalog\Block\Product\List\Related.
 *
 * @magentoDataFixture Magento/Catalog/_files/products_related.php
 */
class RelatedTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');

        $product = $productRepository->get('simple');
        $productWithCross = $productRepository->get('simple_with_cross');
        $objectManager->get('Magento\Framework\Registry')->register('product', $productWithCross);

        /** @var $block \Magento\Catalog\Block\Product\ProductList\Related */
        $block = $objectManager->get('Magento\Framework\View\LayoutInterface')
            ->createBlock('Magento\Catalog\Block\Product\ProductList\Related');

        $block->setLayout($objectManager->get('Magento\Framework\View\LayoutInterface'));
        $block->setTemplate('Magento_Catalog::product/list/items.phtml');
        $block->setType('related');
        $block->addChild('addto', '\Magento\Catalog\Block\Product\ProductList\Item\Container');
        $block->getChildBlock(
            'addto'
        )->addChild(
            'compare',
            '\Magento\Catalog\Block\Product\ProductList\Item\AddTo\Compare',
            ['template' => 'Magento_Catalog::product/list/addto/compare.phtml']
        );

        $html = $block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertContains('Simple Related Product', $html);
        /* name */
        $this->assertContains('"product":"' . $product->getId() .'"', $html);
        /* part of url */
        $this->assertInstanceOf(
            'Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection',
            $block->getItems()
        );
    }
}
