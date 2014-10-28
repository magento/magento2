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
    protected $customerHelperMock;

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

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->storeId = 1;
        $this->customerDataMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\Customer',
            array('getStoreId', 'getCustomAttribute', 'getId', '__wakeup'),
            array(),
            '',
            false
        );
        $this->customerAddressMock = $this->getMock('Magento\Customer\Helper\Address', array(), array(), '', false);
        $this->customerHelperMock = $this->getMock('Magento\Customer\Helper\Data', array(), array(), '', false);
        $this->customerBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            array('mergeDataObjectWithArray'),
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

        $this->model = $this->objectManager->getObject(
            'Magento\Sales\Model\Observer\Frontend\Quote\Address\CollectTotals',
            array(
                'customerAddressHelper' => $this->customerAddressMock,
                'customerHelper' => $this->customerHelperMock,
                'vatValidator' => $this->vatValidatorMock,
                'customerBuilder' => $this->customerBuilderMock
            )
        );
    }

    public function testDispatchWithDisableAutoGroupChange()
    {
        /** @var \Magento\Framework\Service\Data\AttributeValueBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
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
        /** @var \Magento\Framework\Service\Data\AttributeValueBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
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
        /** @var \Magento\Framework\Service\Data\AttributeValueBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
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

        $this->customerHelperMock->expects(
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
        $this->quoteAddressMock->expects($this->never())->method('setPrevQuoteCustomerGroupId');
        $this->customerBuilderMock->expects($this->never())->method('mergeDataObjectWithArray');
        $this->quoteMock->expects($this->never())->method('setCustomerGroupId');

        /** SUT execution */
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithDefaultCustomerGroupId()
    {
        /** @var \Magento\Framework\Service\Data\AttributeValueBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
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
        $this->customerHelperMock->expects(
            $this->once()
        )->method(
            'getDefaultCustomerGroupId'
        )->will(
            $this->returnValue('defaultCustomerGroupId')
        );

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
            $this->returnValue($this->customerDataMock)
        );

        $this->quoteMock->expects($this->once())->method('setCustomerData')->with($this->customerDataMock);

        /** SUT execution */
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithCustomerCountryInEU()
    {
        /** @var \Magento\Framework\Service\Data\AttributeValueBuilder $attributeValueBuilder */
        $attributeValueBuilder = $this->objectManager
            ->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
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

        $this->customerHelperMock->expects($this->once())
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

        $this->customerHelperMock->expects($this->once())
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
            ->will($this->returnValue($this->customerDataMock));
        $this->model->dispatch($this->observerMock);
    }
}
