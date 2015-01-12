<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer\Frontend\Quote\Address;

/**
 * Class CollectTotalsTest
 */
class CollectTotalsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Observer\Frontend\Quote\Address\CollectTotals
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
    protected $customerBuilderMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
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

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
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
        $this->customerBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerDataBuilder',
            ['mergeDataObjectWithArray', 'create'],
            [],
            '',
            false
        );
        $this->vatValidatorMock = $this->getMock(
            'Magento\Sales\Model\Observer\Frontend\Quote\Address\VatValidator',
            [],
            [],
            '',
            false
        );
        $this->observerMock = $this->getMock(
            '\Magento\Framework\Event\Observer',
            ['getQuoteAddress'],
            [],
            '',
            false
        );

        $this->quoteAddressMock = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            ['getCountryId', 'getVatId', 'getQuote', 'setPrevQuoteCustomerGroupId', '__wakeup'],
            [],
            '',
            false,
            false
        );

        $this->quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
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

        $this->observerMock->expects($this->any())
            ->method('getQuoteAddress')
            ->will($this->returnValue($this->quoteAddressMock));

        $this->quoteAddressMock->expects($this->any())->method('getQuote')->will($this->returnValue($this->quoteMock));

        $this->quoteMock->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($this->customerMock));

        $this->customerMock->expects($this->any())->method('getStoreId')->will($this->returnValue($this->storeId));

        $this->model = $this->objectManager->getObject(
            'Magento\Sales\Model\Observer\Frontend\Quote\Address\CollectTotals',
            [
                'customerAddressHelper' => $this->customerAddressMock,
                'customerVat' => $this->customerVatMock,
                'vatValidator' => $this->vatValidatorMock,
                'customerBuilder' => $this->customerBuilderMock,
                'groupManagement' => $this->groupManagementMock
            ]
        );
    }

    public function testDispatchWithDisableAutoGroupChange()
    {
        $this->setAttributeCodeValue('disable_auto_group_change');
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithDisableVatValidator()
    {
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->will($this->returnValue(false));
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithCustomerCountryNotInEUAndNotLoggedCustomerInGroup()
    {
        $this->groupManagementMock->expects($this->once())
            ->method('getNotLoggedInGroup')
            ->will($this->returnValue($this->groupInterfaceMock));
        $this->groupInterfaceMock->expects($this->once())
            ->method('getId')->will($this->returnValue(0));
        $this->setAttributeCodeValue(false);
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
        $this->customerBuilderMock->expects($this->never())->method('mergeDataObjectWithArray');
        $this->quoteMock->expects($this->never())->method('setCustomerGroupId');

        /** SUT execution */
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithDefaultCustomerGroupId()
    {
        $this->setAttributeCodeValue(false);
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
        $this->customerBuilderMock->expects($this->once())
            ->method('mergeDataObjectWithArray')
            ->with($this->customerMock, ['group_id' => 'defaultCustomerGroupId'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);

        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);

        /** SUT execution */
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithCustomerCountryInEU()
    {
        $this->setAttributeCodeValue(false);
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
        $this->customerBuilderMock->expects($this->once())
            ->method('mergeDataObjectWithArray')
            ->with($this->customerMock, ['group_id' => 'customerGroupId'])
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);
        $this->model->dispatch($this->observerMock);
    }

    protected function setAttributeCodeValue($value)
    {
        $attributeInterface = $this->getMockForAbstractClass('Magento\Framework\Api\AttributeInterface', [], '', false);
        $this->customerMock->expects($this->any())
            ->method('getCustomAttribute')
            ->with('disable_auto_group_change')
            ->willReturn($attributeInterface);
        $attributeInterface->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
    }
}
