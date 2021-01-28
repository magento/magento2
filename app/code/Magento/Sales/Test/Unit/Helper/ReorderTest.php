<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Helper;

use \Magento\Sales\Helper\Reorder;

class ReorderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $helper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Sales\Model\Store
     */
    protected $storeParam;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Sales\Model\Order
     */
    protected $orderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Model\Session
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repositoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->helper = new \Magento\Sales\Helper\Reorder(
            $contextMock,
            $this->customerSessionMock,
            $this->repositoryMock
        );

        $this->storeParam = $this->getMockBuilder(\Magento\Sales\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Tests that the store config is checked if orders can be reordered.
     *
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testIsAllowedScopeConfigReorder($scopeConfigValue)
    {
        $this->setupScopeConfigMock($scopeConfigValue);
        $this->assertEquals($scopeConfigValue, $this->helper->isAllowed($this->storeParam));
    }

    /**
     * Tests that the store config is still checked with a null store.
     *
     * @dataProvider getScopeConfigValue
     * @return void
     */
    public function testIsAllowScopeConfigReorderNotAllowWithStore($scopeConfigValue)
    {
        $this->storeParam = null;
        $this->setupScopeConfigMock($scopeConfigValue);
        $this->assertEquals($scopeConfigValue, $this->helper->isAllow());
    }

    /**
     * @return array
     */
    public function getScopeConfigValue()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Sets up the scope config mock with a specified return value.
     *
     * @param bool $returnValue
     * @return void
     */
    protected function setupScopeConfigMock($returnValue)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Reorder::XML_PATH_SALES_REORDER_ALLOW,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeParam
            )
            ->willReturn($returnValue);
    }

    /**
     * Tests that if the store does not allow reorders, it does not matter what the Order returns.
     *
     * @return void
     */
    public function testCanReorderStoreNotAllowed()
    {
        $this->setupOrderMock(false);
        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($this->orderMock);
        $this->assertFalse($this->helper->canReorder(1));
    }

    /**
     * Tests what happens if the customer is not logged in and the store does allow re-orders.
     *
     * @return void
     */
    public function testCanReorderCustomerNotLoggedIn()
    {
        $this->setupOrderMock(true);

        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($this->orderMock);
        $this->assertTrue($this->helper->canReorder(1));
    }

    /**
     * Tests what happens if the customer is logged in and the order does or does not allow reorders.
     *
     * @param bool $orderCanReorder
     * @return void
     * @dataProvider getOrderCanReorder
     */
    public function testCanReorderCustomerLoggedInAndOrderCanReorder($orderCanReorder)
    {
        $this->setupOrderMock(true);

        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->orderMock->expects($this->once())
            ->method('canReorder')
            ->willReturn($orderCanReorder);
        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($this->orderMock);
        $this->assertEquals($orderCanReorder, $this->helper->canReorder(1));
    }

    /**
     * Sets up the order mock to return a store config which will return a specified value on a getValue call.
     *
     * @param bool $storeScopeReturnValue
     * @return void
     */
    protected function setupOrderMock($storeScopeReturnValue)
    {
        $this->setupScopeConfigMock($storeScopeReturnValue);
        $this->orderMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeParam);
    }

    /**
     * @return array
     */
    public function getOrderCanReorder()
    {
        return [
            [true],
            [false]
        ];
    }
}
