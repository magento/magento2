<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList;

/**
 * Test class for \Magento\Catalog\Block\Product\List\Crosssell.
 *
 * @magentoDataFixture Magento/Catalog/_files/products_crosssell.php
 */
class CrosssellTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');

        $firstProduct = $productRepository->get('simple');
        $product = $productRepository->get('simple_with_cross');

        $objectManager->get('Magento\Framework\Registry')->register('product', $product);
        /** @var $block \Magento\Catalog\Block\Product\ProductList\Crosssell */
        $block = $objectManager->get('Magento\Framework\View\LayoutInterface')
            ->createBlock('Magento\Catalog\Block\Product\ProductList\Crosssell');
        $block->setLayout($objectManager->get('Magento\Framework\View\LayoutInterface'));
        $block->setTemplate('Magento_Catalog::product/list/items.phtml');
        $block->setType('crosssell');
        $block->setItemCount(1);
        $html = $block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertContains('Simple Cross Sell', $html);
        /* name */
        $this->assertContains('product\/' . $firstProduct->getId() . '\/', $html);
        /* part of url */
        $this->assertInstanceOf(
            'Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection',
            $block->getItems()
        );
    }
}
