<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Dashboard\Orders;

use Magento\Backend\Block\Template\Context;

class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Grid
     */
    private $block;

    protected function setUp()
    {
        parent::setUp();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $block = $this->createMock(\Magento\Backend\Block\Dashboard\Orders\Grid::class);
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $layout->expects($this->atLeastOnce())->method('getChildName')->willReturn('test');
        $layout->expects($this->atLeastOnce())->method('getBlock')->willReturn($block);
        $context = $objectManager->create(Context::class, ['layout' => $layout]);

        $this->block = $objectManager->create(Grid::class, ['context' => $context]);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGetPreparedCollection()
    {
        $collection = $this->block->getPreparedCollection();
        foreach ($collection->getItems() as $item) {
            if ($item->getIncrementId() == '100000001') {
                $this->assertEquals('firstname lastname', $item->getCustomer());
            }
        }
    }
}
