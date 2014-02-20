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
    protected $customerDataMock;

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
    protected $storeMock;

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

    protected function setUp()
    {
        $this->storeMock = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $this->customerMock = $this->getMock(
            'Magento\Customer\Model\Customer',
            array(
                'getStore',
                'getDisableAutoGroupChange',
                'getId',
                'setGroupId',
                '__wakeup',
            ),
            array(),
            '',
            false
        );
        $this->customerAddressMock = $this->getMock('Magento\Customer\Helper\Address', array(), array(), '', false);
        $this->customerDataMock = $this->getMock('Magento\Customer\Helper\Data', array(), array(), '', false);
        $this->vatValidatorMock = $this->getMock('Magento\Sales\Model\Observer\Frontend\Quote\Address\VatValidator',
            array(), array(), '', false
        );
        $this->observerMock = $this->getMock('\Magento\Event\Observer', array('getQuoteAddress'), array(), '', false);

        $this->quoteAddressMock = $this->getMock('Magento\Sales\Model\Quote\Address',
            array(
                'getCountryId',
                'getVatId',
                'getQuote',
                'setPrevQuoteCustomerGroupId',
                '__wakeup'
            ),
            array(),
            '',
            false,
            false
        );


        $this->quoteMock = $this->getMock('Magento\Sales\Model\Quote',
            array(
                'setCustomerGroupId',
                'getCustomerGroupId',
                'getCustomer',
                '__wakeup',
            ),
            array(),
            '',
            false
        );
        $this->observerMock->expects($this->any())
            ->method('getQuoteAddress')->will($this->returnValue($this->quoteAddressMock));

        $this->quoteAddressMock->expects($this->any())->method('getQuote')->will($this->returnValue($this->quoteMock));

        $this->quoteMock->expects($this->any())->method('getCustomer')->will($this->returnValue($this->customerMock));

        $this->customerMock->expects($this->any())->method('getStore')->will($this->returnValue($this->storeMock));

        $this->model = new \Magento\Sales\Model\Observer\Frontend\Quote\Address\CollectTotals(
            $this->customerAddressMock,
            $this->customerDataMock,
            $this->vatValidatorMock
        );
    }

    public function testDispatchWithDisableAutoGroupChange()
    {
        $this->customerMock->expects($this->once())
            ->method('getDisableAutoGroupChange')
            ->will($this->returnValue(true));

        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithDisableVatValidator()
    {
        $this->customerMock->expects($this->once())
            ->method('getDisableAutoGroupChange')->will($this->returnValue(false));

        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeMock)
            ->will($this->returnValue(false));
        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithCustomerCountryNotInEUAndNotLoggedCustomerInGroup()
    {
        $this->customerMock->expects($this->once())
            ->method('getDisableAutoGroupChange')->will($this->returnValue(false));

        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeMock)
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue('customerCountryCode'));
        $this->quoteAddressMock->expects($this->once())
            ->method('getVatId')->will($this->returnValue('vatId'));

        $this->customerDataMock->expects($this->once())
            ->method('isCountryInEU')
            ->with('customerCountryCode')
            ->will($this->returnValue(false));

        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        $this->quoteAddressMock->expects($this->never())->method('setPrevQuoteCustomerGroupId');
        $this->customerMock->expects($this->never())
            ->method('setGroupId');
        $this->quoteMock->expects($this->never())
            ->method('setCustomerGroupId');

        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithDefaultCustomerGroupId()
    {
        $this->customerMock->expects($this->once())
            ->method('getDisableAutoGroupChange')->will($this->returnValue(false));

        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeMock)
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue('customerCountryCode'));
        $this->quoteAddressMock->expects($this->once())
            ->method('getVatId')->will($this->returnValue(null));

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')->will($this->returnValue('customerGroupId'));

        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue('1'));
        $this->customerDataMock->expects($this->once())
            ->method('getDefaultCustomerGroupId')->will($this->returnValue('defaultCustomerGroupId'));

        $this->quoteAddressMock->expects($this->once())->method('setPrevQuoteCustomerGroupId')->with('customerGroupId');
        $this->customerMock->expects($this->once())
            ->method('setGroupId')->with('defaultCustomerGroupId');
        $this->quoteMock->expects($this->once())
            ->method('setCustomerGroupId')->with('defaultCustomerGroupId');

        $this->model->dispatch($this->observerMock);
    }

    public function testDispatchWithCustomerCountryInEU()
    {
        $this->customerMock->expects($this->once())
            ->method('getDisableAutoGroupChange')->will($this->returnValue(false));

        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeMock)
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue('customerCountryCode'));
        $this->quoteAddressMock->expects($this->once())
            ->method('getVatId')->will($this->returnValue('vatID'));

        $this->customerDataMock->expects($this->once())
            ->method('isCountryInEU')
            ->with('customerCountryCode')
            ->will($this->returnValue(true));

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')->will($this->returnValue('customerGroupId'));

        $validationResult = array('some' => 'result');
        $this->vatValidatorMock->expects($this->once())
            ->method('validate')->with($this->quoteAddressMock, $this->storeMock)
            ->will($this->returnValue($validationResult));

        $this->customerDataMock->expects($this->once())->method('getCustomerGroupIdBasedOnVatNumber')
            ->with('customerCountryCode', $validationResult, $this->storeMock)
            ->will($this->returnValue('customerGroupId'));

        $this->quoteAddressMock->expects($this->once())->method('setPrevQuoteCustomerGroupId')->with('customerGroupId');
        $this->customerMock->expects($this->once())
            ->method('setGroupId')->with('customerGroupId');
        $this->quoteMock->expects($this->once())
            ->method('setCustomerGroupId')->with('customerGroupId');

        $this->model->dispatch($this->observerMock);
    }
}
 
