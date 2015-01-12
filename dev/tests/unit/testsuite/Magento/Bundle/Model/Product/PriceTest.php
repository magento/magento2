<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Bundle\Model\Product\Price
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $groupManagement;

    protected function setUp()
    {
        $this->ruleFactoryMock = $this->getMock(
            '\Magento\CatalogRule\Model\Resource\RuleFactory',
            [],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->localeDateMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->customerSessionMock = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->eventManagerMock = $this->getMock('\Magento\Framework\Event\ManagerInterface');
        $this->catalogHelperMock = $this->getMock('\Magento\Catalog\Helper\Data', [], [], '', false);
        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
        $this->groupManagement = $this->getMockBuilder('Magento\Customer\Api\GroupManagementInterface')
            ->getMockForAbstractClass();

        $this->model = new \Magento\Bundle\Model\Product\Price(
            $this->ruleFactoryMock,
            $this->storeManagerMock,
            $this->localeDateMock,
            $this->customerSessionMock,
            $this->eventManagerMock,
            $this->priceCurrency,
            $this->groupManagement,
            $this->catalogHelperMock
        );
    }

    /**
     * @param float $finalPrice
     * @param float $specialPrice
     * @param int $callsNumber
     * @param bool $dateInInterval
     * @param float $expected
     *
     * @covers \Magento\Bundle\Model\Product\Price::calculateSpecialPrice
     * @covers \Magento\Bundle\Model\Product\Price::__construct
     * @dataProvider calculateSpecialPrice
     */
    public function testCalculateSpecialPrice($finalPrice, $specialPrice, $callsNumber, $dateInInterval, $expected)
    {
        $this->localeDateMock->expects($this->exactly($callsNumber))
            ->method('isScopeDateInInterval')->will($this->returnValue($dateInInterval));

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')->will($this->returnValue($this->storeMock));

        $this->storeMock->expects($this->any())
            ->method('roundPrice')->will($this->returnArgument(0));

        $this->assertEquals(
            $expected,
            $this->model->calculateSpecialPrice($finalPrice, $specialPrice, date('Y-m-d'), date('Y-m-d'))
        );
    }

    /**
     * @return array
     */
    public function calculateSpecialPrice()
    {
        return [
            [10, null, 0, true, 10],
            [10, false, 0, true, 10],
            [10, 50, 1, false, 10],
            [10, 50, 1, true, 5],
            [0, 50, 1, true, 0],
            [10, 100, 1, true, 10],
        ];
    }
}
