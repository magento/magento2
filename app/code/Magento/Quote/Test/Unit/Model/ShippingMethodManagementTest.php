<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use \Magento\Quote\Model\ShippingMethodManagement;

use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ShippingMethodManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingMethodManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingMethodMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodDataFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $this->methodDataFactoryMock = $this->getMock(
            '\Magento\Quote\Api\Data\ShippingMethodInterfaceFactory',
            [
                'create'
            ],
            [],
            '',
            false
        );

        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->quoteMock = $this->getMock(
            '\Magento\Quote\Model\Quote',
            [
                'getShippingAddress',
                'isVirtual',
                'getItemsCount',
                'getQuoteCurrencyCode',
                'getBillingAddress',
                'collectTotals',
                'save',
                '__wakeup',
            ],
            [],
            '',
            false
        );
        $this->shippingAddressMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Address',
            [
                'getCountryId',
                'getShippingMethod',
                'getShippingDescription',
                'getShippingAmount',
                'getBaseShippingAmount',
                'getGroupedAllShippingRates',
                'collectShippingRates',
                'requestShippingRates',
                'setShippingMethod',
                '__wakeup',
                'getShippingRateByCode',
            ],
            [],
            '',
            false
        );
        $this->converterMock = $this->getMock(
            '\Magento\Quote\Model\Cart\ShippingMethodConverter',
            [],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            'Magento\Quote\Model\ShippingMethodManagement',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'methodDataFactory' => $this->methodDataFactoryMock,
                'converter' => $this->converterMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping address not set.
     */
    public function testGetMethodWhenShippingAddressIsNotSet()
    {
        $cartId = 666;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(null));

        $this->assertNull($this->model->get($cartId));
    }

    public function testGetMethod()
    {
        $cartId = 666;
        $countryId = 1;
        $currencyCode = 'US_dollar';
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->quoteMock->expects($this->once())
            ->method('getQuoteCurrencyCode')->willReturn($currencyCode);
        $this->shippingAddressMock->expects($this->any())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddressMock->expects($this->any())
            ->method('getShippingMethod')->will($this->returnValue('one_two'));

        $this->shippingAddressMock->expects($this->once())->method('collectShippingRates')->willReturnSelf();
        $shippingRateMock = $this->getMock('\Magento\Quote\Model\Quote\Address\Rate', [], [], '', false);

        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with('one_two')
            ->willReturn($shippingRateMock);

        $this->shippingMethodMock = $this->getMock('\Magento\Quote\Api\Data\ShippingMethodInterface');
        $this->converterMock->expects($this->once())
            ->method('modelToDataObject')
            ->with($shippingRateMock, $currencyCode)
            ->willReturn($this->shippingMethodMock);
        $this->model->get($cartId);
    }

    public function testGetMethodIfMethodIsNotSet()
    {
        $cartId = 666;
        $countryId = 1;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->any())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddressMock->expects($this->any())
            ->method('getShippingMethod')->will($this->returnValue(null));

        $this->assertNull($this->model->get($cartId));
    }

    public function testGetListForVirtualCart()
    {
        $cartId = 834;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')->will($this->returnValue(true));

        $this->assertEquals([], $this->model->getList($cartId));
    }

    public function testGetListForEmptyCart()
    {
        $cartId = 834;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')->will($this->returnValue(0));

        $this->assertEquals([], $this->model->getList($cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping address not set.
     */
    public function testGetListWhenShippingAddressIsNotSet()
    {
        $cartId = 834;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')->will($this->returnValue(3));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(null));

        $this->model->getList($cartId);
    }

    public function testGetList()
    {
        $cartId = 834;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')->will($this->returnValue(3));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(345));
        $this->shippingAddressMock->expects($this->once())->method('collectShippingRates');
        $shippingRateMock = $this->getMock('\Magento\Quote\Model\Quote\Address\Rate', [], [], '', false);
        $this->shippingAddressMock->expects($this->once())
            ->method('getGroupedAllShippingRates')
            ->will($this->returnValue([[$shippingRateMock]]));

        $currencyCode = 'EUR';
        $this->quoteMock->expects($this->once())
            ->method('getQuoteCurrencyCode')
            ->will($this->returnValue($currencyCode));

        $this->converterMock->expects($this->once())
            ->method('modelToDataObject')
            ->with($shippingRateMock, $currencyCode)
            ->will($this->returnValue('RateValue'));
        $this->assertEquals(['RateValue'], $this->model->getList($cartId));
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
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(0));
        $this->quoteMock->expects($this->never())->method('isVirtual');

        $this->model->set($cartId, $carrierCode, $methodCode);
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

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(true));

        $this->model->set($cartId, $carrierCode, $methodCode);
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
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(null));

        $this->model->set($cartId, $carrierCode, $methodCode);
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
        $countryId = 1;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddressMock->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')->will($this->returnValue(false));
        $this->shippingAddressMock->expects($this->never())->method('save');

        $this->model->set($cartId, $carrierCode, $methodCode);
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
        $countryId = 1;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddressMock->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')->will($this->returnValue(true));
        $exception = new \Exception('Custom Error');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnSelf());
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);

        $this->model->set($cartId, $carrierCode, $methodCode);
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
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId');

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    public function testSetMethod()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $countryId = 1;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddressMock->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')->will($this->returnValue(true));
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnSelf());
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);

        $this->assertTrue($this->model->set($cartId, $carrierCode, $methodCode));
    }
}
