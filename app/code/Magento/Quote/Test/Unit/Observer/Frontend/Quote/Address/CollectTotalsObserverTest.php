<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Test\Unit\Observer\Frontend\Quote\Address;

/**
 * Class CollectTotalsTest
 */
class CollectTotalsObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver;
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerVatMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeId;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $vatValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDataFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupInterfaceMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeId = 1;
        $this->customerMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            '',
            false,
            true,
            true,
            ['getStoreId', 'getCustomAttribute', 'getId', '__wakeup']
        );
        $this->customerAddressMock = $this->getMock('Magento\Customer\Helper\Address', [], [], '', false);
        $this->customerVatMock = $this->getMock('Magento\Customer\Model\Vat', [], [], '', false);
        $this->customerDataFactoryMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterfaceFactory',
            ['mergeDataObjectWithArray', 'create'],
            [],
            '',
            false
        );
        $this->vatValidatorMock = $this->getMock(
            'Magento\Quote\Observer\Frontend\Quote\Address\VatValidator',
            [],
            [],
            '',
            false
        );
        $this->observerMock = $this->getMock(
            '\Magento\Framework\Event\Observer',
            ['getShippingAssignment', 'getQuote'],
            [],
            '',
            false
        );

        $this->quoteAddressMock = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            ['getCountryId', 'getVatId', 'getQuote', 'setPrevQuoteCustomerGroupId', '__wakeup'],
            [],
            '',
            false,
            false
        );

        $this->quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            ['setCustomerGroupId', 'getCustomerGroupId', 'getCustomer', '__wakeup', 'setCustomer'],
            [],
            '',
            false
        );

        $this->groupManagementMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\GroupManagementInterface',
            [],
            '',
            false,
            true,
            true,
            [
                'getDefaultGroup',
                'getNotLoggedInGroup'
            ]
        );

        $this->groupInterfaceMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\GroupInterface',
            [],
            '',
            false,
            true,
            true,
            ['getId']
        );

        $shippingAssignmentMock = $this->getMock('\Magento\Quote\Api\Data\ShippingAssignmentInterface');
        $shippingMock = $this->getMock('\Magento\Quote\Api\Data\ShippingInterface');
        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);
        $shippingMock->expects($this->once())->method('getAddress')->willReturn($this->quoteAddressMock);

        $this->observerMock->expects($this->once())
            ->method('getShippingAssignment')
            ->willReturn($shippingAssignmentMock);

        $this->observerMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($this->customerMock));

        $this->customerMock->expects($this->any())->method('getStoreId')->will($this->returnValue($this->storeId));

        $this->model = new \Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver(
            $this->customerAddressMock,
            $this->customerVatMock,
            $this->vatValidatorMock,
            $this->customerDataFactoryMock,
            $this->groupManagementMock
        );
    }

    public function testDispatchWithDisableVatValidator()
    {
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->will($this->returnValue(false));
        $this->model->execute($this->observerMock);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testDispatchWithCustomerCountryNotInEUAndNotLoggedCustomerInGroup()
    {
        $this->groupManagementMock->expects($this->once())
            ->method('getNotLoggedInGroup')
            ->will($this->returnValue($this->groupInterfaceMock));
        $this->groupInterfaceMock->expects($this->once())
            ->method('getId')->will($this->returnValue(0));
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('customerCountryCode'));
        $this->quoteAddressMock->expects($this->once())->method('getVatId')->will($this->returnValue('vatId'));

        $this->customerVatMock->expects(
            $this->once()
        )->method(
            'isCountryInEU'
        )->with(
            'customerCountryCode'
        )->will(
            $this->returnValue(false)
        );

        $groupMock = $this->getMockBuilder('Magento\Customer\Api\Data\GroupInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        /** Assertions */
        $this->quoteAddressMock->expects($this->never())->method('setPrevQuoteCustomerGroupId');
        $this->customerDataFactoryMock->expects($this->never())->method('mergeDataObjectWithArray');
        $this->quoteMock->expects($this->never())->method('setCustomerGroupId');

        /** SUT execution */
        $this->model->execute($this->observerMock);
    }

    public function testDispatchWithDefaultCustomerGroupId()
    {
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('customerCountryCode'));
        $this->quoteAddressMock->expects($this->once())->method('getVatId')->will($this->returnValue(null));

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue('customerGroupId')
        );
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue('1'));
        $this->groupManagementMock->expects($this->once())
            ->method('getDefaultGroup')
            ->will($this->returnValue($this->groupInterfaceMock));
        $this->groupInterfaceMock->expects($this->once())
            ->method('getId')->will($this->returnValue('defaultCustomerGroupId'));
        /** Assertions */
        $this->quoteAddressMock->expects($this->once())
            ->method('setPrevQuoteCustomerGroupId')
            ->with('customerGroupId');
        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('defaultCustomerGroupId');
        $this->customerDataFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);

        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);

        /** SUT execution */
        $this->model->execute($this->observerMock);
    }

    public function testDispatchWithCustomerCountryInEU()
    {
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('customerCountryCode'));
        $this->quoteAddressMock->expects($this->once())
            ->method('getVatId')
            ->will($this->returnValue('vatID'));

        $this->customerVatMock->expects($this->once())
            ->method('isCountryInEU')
            ->with('customerCountryCode')
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue('customerGroupId'));

        $validationResult = ['some' => 'result'];
        $this->vatValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->quoteAddressMock, $this->storeId)
            ->will($this->returnValue($validationResult));

        $this->customerVatMock->expects($this->once())
            ->method('getCustomerGroupIdBasedOnVatNumber')
            ->with('customerCountryCode', $validationResult, $this->storeId)
            ->will($this->returnValue('customerGroupId'));

        /** Assertions */
        $this->quoteAddressMock->expects($this->once())
            ->method('setPrevQuoteCustomerGroupId')
            ->with('customerGroupId');

        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('customerGroupId');
        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        $this->customerDataFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);
        $this->model->execute($this->observerMock);
    }
}
