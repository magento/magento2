<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\SalesRule;

class CalculatorForTest extends Calculator
{
    public function setValidatorUtility($validatorUtility)
    {
        $this->validatorUtility = $validatorUtility;
    }
}

class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\SalesRule\CalculatorForTest|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \Magento\Sales\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \Magento\SalesRule\Model\Utility|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $utilityMock;

    /**
     * @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleMock;

    /**
     * @var \Magento\Sales\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    protected function setUp()
    {
        $this->utilityMock = $this->getMockBuilder('Magento\SalesRule\Model\Utility')
            ->disableOriginalConstructor()
            ->setMethods(['canProcessRule'])
            ->getMock();

        $this->_model = $this->getMockBuilder('Magento\OfflineShipping\Model\SalesRule\CalculatorForTest')
            ->disableOriginalConstructor()
            ->setMethods(['_getRules', '__wakeup'])
            ->getMock();

        $this->ruleMock = $this->getMockBuilder('Magento\SalesRule\Model\Rule')
            ->disableOriginalConstructor()
            ->setMethods(['getActions', 'getSimpleFreeShipping'])
            ->getMock();

        $this->addressMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['setFreeShipping'])
            ->getMock();

        $this->_model->setValidatorUtility($this->utilityMock);

        $this->itemMock = $this->getMock('Magento\Sales\Model\Quote\Item', ['getAddress'], [], '', false);

        $this->itemMock->expects($this->once())
            ->method('getAddress')
            ->willReturn($this->addressMock);
    }

    public function testProcessFreeShipping()
    {
        $this->_model->expects($this->any())->method('_getRules')->will($this->returnValue([]));

        $this->assertInstanceOf(
            'Magento\OfflineShipping\Model\SalesRule\Calculator',
            $this->_model->processFreeShipping($this->itemMock)
        );
    }

    public function testProcessFreeShippingContinueOnProcessRule()
    {
        $this->_model->expects($this->once())
            ->method('_getRules')
            ->willReturn(['rule1']);

        $this->utilityMock->expects($this->once())
            ->method('canProcessRule')
            ->willReturn(false);

        $this->_model->processFreeShipping($this->itemMock);
    }

    public function testProcessFreeShippingContinueOnValidateItem()
    {
        $this->utilityMock->expects($this->once())
            ->method('canProcessRule')
            ->willReturn(true);

        $actionsCollectionMock = $this->getMockBuilder('Magento\Rule\Model\Action\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();

        $actionsCollectionMock->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        $this->ruleMock->expects($this->once())
            ->method('getActions')
            ->willReturn($actionsCollectionMock);

        $this->_model->expects($this->once())
            ->method('_getRules')
            ->willReturn([$this->ruleMock]);

        $this->_model->processFreeShipping($this->itemMock);
    }

    /**
     * @dataProvider rulesDataProvider
     */
    public function testProcessFreeShippingFreeShippingItem($rule)
    {
        $this->utilityMock->expects($this->once())
            ->method('canProcessRule')
            ->willReturn(true);

        $actionsCollectionMock = $this->getMockBuilder('Magento\Rule\Model\Action\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();

        $actionsCollectionMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $this->ruleMock->expects($this->once())
            ->method('getActions')
            ->willReturn($actionsCollectionMock);

        $this->_model->expects($this->once())
            ->method('_getRules')
            ->willReturn([$this->ruleMock]);

        $this->ruleMock->expects($this->once())
            ->method('getSimpleFreeShipping')
            ->willReturn($rule);

        $this->addressMock->expects(
            $rule == \Magento\OfflineShipping\Model\SalesRule\Rule::FREE_SHIPPING_ADDRESS ? $this->once() : $this->never()
        )->method('setFreeShipping');

        $this->_model->processFreeShipping($this->itemMock);
    }

    public function rulesDataProvider()
    {
        return [
            [\Magento\OfflineShipping\Model\SalesRule\Rule::FREE_SHIPPING_ITEM],
            [\Magento\OfflineShipping\Model\SalesRule\Rule::FREE_SHIPPING_ADDRESS]
        ];
    }
}
