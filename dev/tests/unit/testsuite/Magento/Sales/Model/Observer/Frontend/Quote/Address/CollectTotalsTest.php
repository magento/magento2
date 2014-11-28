<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Observer\Frontend\Quote\Address;

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
    protected $customerDataMock;

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

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $groupManagement;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->storeId = 1;
        $this->customerDataMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->setMethods(['getStoreId', 'getCustomAttribute', 'getId', '__wakeup'])
            ->getMockForAbstractClass();
        $this->customerAddressMock = $this->getMock('Magento\Customer\Helper\Address', array(), array(), '', false);
        $this->customerVatMock = $this->getMock('Magento\Customer\Model\Vat', array(), array(), '', false);
        $this->customerBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerDataBuilder',
            array('mergeDataObjectWithArray', 'create'),
            array(),
            '',
            false
        );
        $this->vatValidatorMock = $this->getMock(
            'Magento\Sales\Model\Observer\Frontend\Quote\Address\VatValidator',
            array(),
            array(),
            '',
            false
        );
        $this->observerMock = $this->getMock(
            '\Magento\Framework\Event\Observer',
            array('getQuoteAddress'),
            array(),
            '',
            false
        );

        $this->quoteAddressMock = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            array('getCountryId', 'getVatId', 'getQuote', 'setPrevQuoteCustomerGroupId', '__wakeup'),
            array(),
            '',
            false,
            false
        );


        $this->quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
            array('setCustomerGroupId', 'getCustomerGroupId', 'getCustomerData', 'setCustomerData', '__wakeup'),
            array(),
            '',
            false
        );
        $this->observerMock->expects(
            $this->any()
        )->method(
            'getQuoteAddress'
        )->will(
            $this->returnValue($this->quoteAddressMock)
        );

        $this->quoteAddressMock->expects($this->any())->method('getQuote')->will($this->returnValue($this->quoteMock));

        $this->quoteMock->expects(
            $this->any()
        )->method(
            'getCustomerData'
        )->will(
            $this->returnValue($this->customerDataMock)
        );

        $this->customerDataMock->expects($this->any())->method('getStoreId')->will($this->returnValue($this->storeId));

        $this->groupManagement = $this->getMock(
            'Magento\Customer\Api\GroupManagementInterface',
            ['getDefaultGroup', 'getNotLoggedInGroup', 'isReadOnly', 'getLoggedInGroups', 'getAllCustomersGroup'],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            'Magento\Sales\Model\Observer\Frontend\Quote\Address\CollectTotals',
            array(
                'customerAddressHelper' => $this->customerAddressMock,
                'customerVat' => $this->customerVatMock,
                'vatValidator' => $this->vatValidatorMock,
                'customerBuilder' => $this->customerBuilderMock,
                'groupManagement' => $this->groupManagement
            )
        );
    }

    public function testDispatchWithDisableAutoGroupChange()
    {
        /** @var \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Api\AttributeDataBuilder');
        $attributeValueBuilder->setAttributeCode('disable_auto_group_change')->setValue(true);
        $this->customerDataMock->expects(
            $this->exactly(2)
        )->method(
            'getCustomAttribute'
        )->with(
            'disable_auto_group_change'
        )->will(
            $this->returnValue($attributeValueBuilder->create())
        );

        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithDisableVatValidator()
    {
        /** @var \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Api\AttributeDataBuilder');
        $attributeValueBuilder->setAttributeCode('disable_auto_group_change')->setValue(false);
        $this->customerDataMock->expects(
            $this->exactly(2)
        )->method(
            'getCustomAttribute'
        )->with(
            'disable_auto_group_change'
        )->will(
            $this->returnValue($attributeValueBuilder->create())
        );

        $this->vatValidatorMock->expects(
            $this->once()
        )->method(
            'isEnabled'
        )->with(
            $this->quoteAddressMock,
            $this->storeId
        )->will(
            $this->returnValue(false)
        );
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithCustomerCountryNotInEUAndNotLoggedCustomerInGroup()
    {
        /** @var \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Api\AttributeDataBuilder');
        $attributeValueBuilder->setAttributeCode('disable_auto_group_change')->setValue(false);
        /** Preconditions */
        $this->customerDataMock->expects(
            $this->exactly(2)
        )->method(
            'getCustomAttribute'
        )->with(
            'disable_auto_group_change'
        )->will(
            $this->returnValue($attributeValueBuilder->create())
        );

        $this->vatValidatorMock->expects(
            $this->once()
        )->method(
            'isEnabled'
        )->with(
            $this->quoteAddressMock,
            $this->storeId
        )->will(
            $this->returnValue(true)
        );

        $this->quoteAddressMock->expects(
            $this->once()
        )->method(
            'getCountryId'
        )->will(
            $this->returnValue('customerCountryCode')
        );
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

        $this->customerDataMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        /** Assertions */
        $this->quoteAddressMock->expects($this->once())->method('setPrevQuoteCustomerGroupId');

        $this->customerBuilderMock->expects($this->once())
            ->method('mergeDataObjectWithArray')
            ->will($this->returnSelf());

        $this->customerBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerDataMock));

        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('notLoggedInGroupId');

        $notLoggedInGroup = $this->getMock(
            'Magento\Customer\Model\Group',
            ['getId'],
            [],
            '',
            false
        );
        $notLoggedInGroup->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('notLoggedInGroupId'));
        $this->groupManagement->expects($this->any())
            ->method('getNotLoggedInGroup')
            ->will($this->returnValue($notLoggedInGroup));

        /** SUT execution */
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithDefaultCustomerGroupId()
    {
        /** @var \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Api\AttributeDataBuilder');
        $attributeValueBuilder->setAttributeCode('disable_auto_group_change')->setValue(false);
        /** Preconditions */
        $this->customerDataMock->expects(
            $this->exactly(2)
        )->method(
            'getCustomAttribute'
        )->with(
            'disable_auto_group_change'
        )->will(
            $this->returnValue($attributeValueBuilder->create())
        );

        $this->vatValidatorMock->expects(
            $this->once()
        )->method(
            'isEnabled'
        )->with(
            $this->quoteAddressMock,
            $this->storeId
        )->will(
            $this->returnValue(true)
        );

        $this->quoteAddressMock->expects(
            $this->once()
        )->method(
            'getCountryId'
        )->will(
            $this->returnValue('customerCountryCode')
        );
        $this->quoteAddressMock->expects($this->once())->method('getVatId')->will($this->returnValue(null));

        $this->quoteMock->expects(
            $this->once()
        )->method(
            'getCustomerGroupId'
        )->will(
            $this->returnValue('customerGroupId')
        );

        $this->customerDataMock->expects($this->once())->method('getId')->will($this->returnValue('1'));

        $defaultCustomerGroup = $this->getMock(
            'Magento\Customer\Model\Group',
            ['getId'],
            [],
            '',
            false
        );
        $defaultCustomerGroup->expects($this->once())->method('getId')
            ->will($this->returnValue('defaultCustomerGroupId'));
        $this->groupManagement->expects($this->any())->method('getDefaultGroup')->with($this->storeId)
            ->will($this->returnValue($defaultCustomerGroup));

        /** Assertions */
        $this->quoteAddressMock->expects(
            $this->once()
        )->method(
            'setPrevQuoteCustomerGroupId'
        )->with(
            'customerGroupId'
        );
        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('defaultCustomerGroupId');
        $this->customerBuilderMock->expects(
            $this->once()
        )->method(
            'mergeDataObjectWithArray'
        )->with(
            $this->customerDataMock,
            array('group_id' => 'defaultCustomerGroupId')
        )->will(
            $this->returnValue($this->customerBuilderMock)
        );
        $this->customerBuilderMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->customerDataMock)
        );

        $this->quoteMock->expects($this->once())->method('setCustomerData')->with($this->customerDataMock);

        /** SUT execution */
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithCustomerCountryInEU()
    {
        /** @var \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Api\AttributeDataBuilder');
        $attributeValueBuilder->setAttributeCode('disable_auto_group_change')->setValue(false);
        /** Preconditions */
        $this->customerDataMock->expects($this->exactly(2))
            ->method('getCustomAttribute')
            ->with('disable_auto_group_change')
            ->will($this->returnValue($attributeValueBuilder->create()));

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
            ->will($this->returnValue($attributeValueBuilder->create()));

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue('customerGroupId'));

        $validationResult = array('some' => 'result');
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
        $this->customerBuilderMock->expects($this->once())
            ->method('mergeDataObjectWithArray')
            ->with($this->customerDataMock, array('group_id' => 'customerGroupId'))
            ->will($this->returnValue($this->customerBuilderMock));
        $this->customerBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerDataMock));
        $this->model->dispatch($this->observerMock);
    }
}
