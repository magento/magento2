<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product');
        $product->load(2);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('product', $product);
        /** @var $block \Magento\Catalog\Block\Product\ProductList\Crosssell */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Catalog\Block\Product\ProductList\Crosssell'
        );
        $block->setLayout(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\View\LayoutInterface')
        );
        $block->setTemplate('Magento_Catalog::product/list/items.phtml');
        $block->setType('crosssell');
        $block->setItemCount(1);
        $html = $block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertContains('Simple Cross Sell', $html);
        /* name */
        $this->assertContains('product\/1\/', $html);
        /* part of url */
        $this->assertInstanceOf(
            'Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection',
            $block->getItems()
        );
    }
}
