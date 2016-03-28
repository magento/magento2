<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Shopcart\Product;

use Magento\Quote\Model\Quote\Item;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Reports\Block\Adminhtml\Shopcart\Product\Grid
 *
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Sales/_files/quote.php
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class GridTest extends \Magento\Reports\Block\Adminhtml\Shopcart\GridTestAbstract
{
    /**
     * @return void
     */
    public function testGridContent()
    {
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get('Magento\Framework\View\LayoutInterface');
        /** @var Grid $grid */
        $grid = $layout->createBlock('Magento\Reports\Block\Adminhtml\Shopcart\Product\Grid');
        $result = $grid->getPreparedCollection();

        $this->assertCount(1, $result->getItems());
        /** @var Item $quoteItem */
        $quoteItem = $result->getFirstItem();
        $this->assertInstanceOf('Magento\Quote\Model\Quote\Item', $quoteItem);

        $this->assertGreaterThan(0, (int)$quoteItem->getProductId());
        $this->assertEquals('Simple Product', $quoteItem->getName());
    }
}
