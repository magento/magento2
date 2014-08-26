<?php
/** 
 * 
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

namespace Magento\Checkout\Service\V1\ShippingMethod;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WriteService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

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
    protected $shippingAddressMock;

    protected function setUp()
    {
        $objectManager =new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->addressFactoryMock = $this->getMock('\Magento\Sales\Model\Quote\AddressFactory', [], [], '', false);
        $this->quoteLoaderMock = $this->getMock('\Magento\Checkout\Service\V1\QuoteLoader', [], [], '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->quoteMock = $this->getMock(
            '\Magento\Sales\Model\Quote',
            [
                'getItemsCount',
                'isVirtual',
                'getShippingAddress',
                'getBillingAddress',
                'collectTotals',
                'save',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->shippingAddressMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Address',
            [
                'setShippingMethod',
                'requestShippingRates',
                'save',
                'getCountryId',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $this->service = $objectManager->getObject(
            'Magento\Checkout\Service\V1\ShippingMethod\WriteService',
            [
                'addressFactory' => $this->addressFactoryMock,
                'quoteLoader' => $this->quoteLoaderMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Shipping method is not applicable for empty cart
     */
    public function testSetMethodWithInputException()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $storeId = 78;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteLoaderMock->expects($this->once())
            ->method('load')->with($cartId, $storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(0));
        $this->quoteMock->expects($this->never())->method('isVirtual');

        $this->service->setMethod($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart contains virtual product(s) only. Shipping method is not applicable.
     */
    public function testSetMethodWithVirtualProduct()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $storeId = 78;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteLoaderMock->expects($this->once())
            ->method('load')->with($cartId, $storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(true));

        $this->service->setMethod($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping address is not set
     */
    public function testSetMethodWithoutShippingAddress()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $storeId = 78;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteLoaderMock->expects($this->once())
            ->method('load')->with($cartId, $storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(null));

        $this->service->setMethod($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Billing address is not set
     */
    public function testSetMethodWithoutBillingAddress()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $storeId = 78;
        $countryId = 1;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteLoaderMock->expects($this->once())
            ->method('load')->with($cartId, $storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $billingAddressMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Address',
            ['getCountryId', '__wakeup'],
            [],
            '',
            false
        );
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')->will($this->returnValue($billingAddressMock));
        $billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(null));

        $this->service->setMethod($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Carrier with such method not found: 34, 56
     */
    public function testSetMethodWithNotFoundMethod()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $storeId = 78;
        $countryId = 1;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteLoaderMock->expects($this->once())
            ->method('load')->with($cartId, $storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $billingAddressMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Address',
            ['getCountryId', '__wakeup'],
            [],
            '',
            false
        );
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')->will($this->returnValue($billingAddressMock));
        $billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(23));
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddressMock->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->shippingAddressMock->expects($this->once())
            ->method('requestShippingRates')->will($this->returnValue(false));
        $this->shippingAddressMock->expects($this->never())->method('save');

        $this->service->setMethod($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Cannot set shipping method. Custom Error
     */
    public function testSetMethodWithCouldNotSaveException()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $storeId = 78;
        $countryId = 1;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteLoaderMock->expects($this->once())
            ->method('load')->with($cartId, $storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $billingAddressMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Address',
            [
                'getCountryId',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')->will($this->returnValue($billingAddressMock));
        $billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(23));
        $this->shippingAddressMock->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->shippingAddressMock->expects($this->once())
            ->method('requestShippingRates')->will($this->returnValue(true));
        $exception = new \Exception('Custom Error');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnSelf());
        $this->quoteMock->expects($this->once())->method('save')->will($this->throwException($exception));

        $this->service->setMethod($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping address is not set
     */
    public function testSetMethodWithoutAddress()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $storeId = 78;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteLoaderMock->expects($this->once())
            ->method('load')->with($cartId, $storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId');

        $this->service->setMethod($cartId, $carrierCode, $methodCode);
    }

    public function testSetMethod()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $storeId = 78;
        $countryId = 1;

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->quoteLoaderMock->expects($this->once())
            ->method('load')->with($cartId, $storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $billingAddressMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Address',
            [
                'getCountryId',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')->will($this->returnValue($billingAddressMock));
        $billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(23));
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddressMock->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->shippingAddressMock->expects($this->once())
            ->method('requestShippingRates')->will($this->returnValue(true));
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnSelf());
        $this->quoteMock->expects($this->once())->method('save');

        $this->assertTrue($this->service->setMethod($cartId, $carrierCode, $methodCode));
    }
}
