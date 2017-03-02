<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $layout = Bootstrap::getObjectManager()->get(\Magento\Framework\View\LayoutInterface::class);
        /** @var Grid $grid */
        $grid = $layout->createBlock(\Magento\Reports\Block\Adminhtml\Shopcart\Product\Grid::class);
        $result = $grid->getPreparedCollection();

        $this->assertCount(1, $result->getItems());
        /** @var Item $quoteItem */
        $quoteItem = $result->getFirstItem();
        $this->assertInstanceOf(\Magento\Quote\Model\Quote\Item::class, $quoteItem);

        $this->assertGreaterThan(0, (int)$quoteItem->getProductId());
        $this->assertEquals('Simple Product', $quoteItem->getName());
    }
}
