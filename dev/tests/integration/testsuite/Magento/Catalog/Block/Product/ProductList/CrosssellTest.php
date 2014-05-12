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
        $this->assertInstanceOf('Magento\Catalog\Model\Resource\Product\Link\Product\Collection', $block->getItems());
    }
}
