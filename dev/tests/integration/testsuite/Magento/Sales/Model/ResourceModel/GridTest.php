<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use Magento\TestFramework\Helper\Bootstrap;

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
        $this->objectManager = Bootstrap::getObjectManager();
        $this->resourceModel = $this->objectManager->get('Magento\Sales\Model\ResourceModel\Order\Grid');
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
     * Tests asynchronous insertion of the new entity into order grid.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
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
     * Returns sales order grid items.
     *
     * @return \Magento\Framework\DataObject[]
     */
    private function getOrderGridItemList()
    {
        /** @var Collection $orderGridCollection */
        $orderGridCollection = $this->objectManager->get(Collection::class);

        return $orderGridCollection->addFilter('increment_id', '100000001')->getItems();
    }
}
