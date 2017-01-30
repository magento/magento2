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
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $product->load(2);
        $objectManager->get('Magento\Framework\Registry')->register('product', $product);

        /** @var $block \Magento\Catalog\Block\Product\ProductList\Related */
        $block = $objectManager->get('Magento\Framework\View\LayoutInterface')
            ->createBlock('Magento\Catalog\Block\Product\ProductList\Related');
        $block->setLayout($objectManager->get('Magento\Framework\View\LayoutInterface'));
        $block->setTemplate('Magento_Catalog::product/list/items.phtml');
        $block->setType('related');

        $html = $block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertContains('Simple Related Product', $html);
        /* name */
        $this->assertContains('"product":"1"', $html);
        /* part of url */
        $this->assertInstanceOf(
            'Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection',
            $block->getItems()
        );
    }
}
