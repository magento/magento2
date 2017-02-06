<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

/**
 * Class GridTest
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Grid
     */
    private $resourceModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resourceModel = $this->objectManager->create('Magento\Sales\Model\ResourceModel\Order\Grid');
    }

    /**
     * Tests asynchronous insertion of the new entity into order grid.
     *
     * @magentoDataFixture Magento/Sales/_files/order_async.php
     */
    public function testRefreshByScheduleAsyncModeSuccess()
    {
        $this->resourceModel->refreshBySchedule();
        $this->assertNotEmpty($this->getOrderGridItemList());
    }

    /**
     * Tests failing of asynchronous insertion new entity into order grid.
     *
     * @magentoDataFixture Magento/Sales/_files/order_async.php
     */
    public function testRefreshByScheduleAsyncModeFail()
    {
        $this->assertEmpty($this->getOrderGridItemList());
    }

    /**
     * Tests synchronous insertion of the new entity into order grid.
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRefreshByScheduleSyncModeSuccess()
    {
        $this->assertNotEmpty($this->getOrderGridItemList());
    }

    /**
     * Returns value of signifyd_guarantee_status column from sales order grid
     *
     * @return string|null
     */
    private function getOrderGridItemList()
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $orderGridCollection */
        $orderGridCollection = $this->objectManager->get(
            \Magento\Sales\Model\ResourceModel\Order\Grid\Collection::class
        );

        return $orderGridCollection->addFilter('increment_id', '100000001')->getItems();
    }
}
