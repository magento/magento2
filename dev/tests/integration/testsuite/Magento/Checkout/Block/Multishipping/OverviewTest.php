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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Block\Multishipping;

/**
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class OverviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Multishipping\Overview
     */
    protected $_block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->getArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)->load();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\LayoutInterface')
            ->createBlock('Magento\Checkout\Block\Multishipping\Overview');
    }

    public function testGetRowItemHtml()
    {
        /** @var $item \Magento\Sales\Model\Quote\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Sales\Model\Quote\Item');
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product');
        $product->load(1);
        $item->setProduct($product);
        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Sales\Model\Quote');
        $item->setQuote($quote);
        // assure that default renderer was obtained
        $this->assertSelectCount('strong.product.name a', 1, $this->_block->getRowItemHtml($item));
    }
}
