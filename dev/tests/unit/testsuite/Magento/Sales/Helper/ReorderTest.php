<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Helper;

class ReorderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Sales\Model\Store
     */
    protected $storeParam;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Sales\Model\Order
     */
    protected $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Session
     */
    protected $customerSessionMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $contextMock = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $appStateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $pricingCurrencyMock = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new \Magento\Sales\Helper\Reorder(
            $contextMock,
            $this->scopeConfigMock,
            $storeManagerMock,
            $appStateMock,
            $pricingCurrencyMock,
            $this->customerSessionMock
        );

        $this->storeParam = $this->getMockBuilder('Magento\Sales\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
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
            ->will($this->returnValue($returnValue));
    }

    /**
     * Tests that if the store does not allow reorders, it does not matter what the Order returns.
     *
     * @return void
     */
    public function testCanReorderStoreNotAllowed()
    {
        $this->setupOrderMock(false);
        $this->assertFalse($this->helper->canReorder($this->orderMock));
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
            ->will($this->returnValue(false));

        $this->assertTrue($this->helper->canReorder($this->orderMock));
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
            ->will($this->returnValue(true));

        $this->orderMock->expects($this->once())
            ->method('canReorder')
            ->will($this->returnValue($orderCanReorder));

        $this->assertEquals($orderCanReorder, $this->helper->canReorder($this->orderMock));
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
            ->will($this->returnValue($this->storeParam));
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
