<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Service\V1\ShippingMethod;

use Magento\Checkout\Service\V1\Data\Cart\ShippingMethod;
use Magento\TestFramework\Helper\ObjectManager;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

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
    protected $methodBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->methodBuilderMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\ShippingMethodBuilder',
            ['populateWithArray', 'create'],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->quoteMock = $this->getMock(
            '\Magento\Sales\Model\Quote',
            [
                'getShippingAddress',
                'isVirtual',
                'getItemsCount',
                'getQuoteCurrencyCode',
                '__wakeup',
            ],
            [],
            '',
            false
        );
        $this->shippingAddressMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Address',
            [
                'getCountryId',
                'getShippingMethod',
                'getShippingDescription',
                'getShippingAmount',
                'getBaseShippingAmount',
                'getGroupedAllShippingRates',
                'collectShippingRates',
                '__wakeup',
            ],
            [],
            '',
            false
        );
        $this->converterMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\ShippingMethodConverter',
            [],
            [],
            '',
            false
        );

        $this->service = $this->objectManager->getObject(
            'Magento\Checkout\Service\V1\ShippingMethod\ReadService',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'methodBuilder' => $this->methodBuilderMock,
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

        $this->assertNull($this->service->getMethod($cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Line "WrongShippingMethod" doesn't contain delimiter _
     */
    public function testGetMethodWhenShippingMethodIsInvalid()
    {
        $cartId = 884;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(34));
        $this->shippingAddressMock->expects($this->exactly(2))
            ->method('getShippingMethod')
            ->will($this->returnValue('WrongShippingMethod'));

        $this->assertNull($this->service->getMethod($cartId));
    }

    public function testGetMethod()
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
            ->method('getShippingMethod')->will($this->returnValue('one_two'));
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingDescription')->will($this->returnValue('carrier - method'));
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingAmount')->will($this->returnValue(123.56));
        $this->shippingAddressMock->expects($this->once())
            ->method('getBaseShippingAmount')->will($this->returnValue(100.06));
        $output = [
            ShippingMethod::CARRIER_CODE => 'one',
            ShippingMethod::METHOD_CODE => 'two',
            ShippingMethod::CARRIER_TITLE => 'carrier',
            ShippingMethod::METHOD_TITLE => 'method',
            ShippingMethod::SHIPPING_AMOUNT => 123.56,
            ShippingMethod::BASE_SHIPPING_AMOUNT => 100.06,
            ShippingMethod::AVAILABLE => true,
        ];
        $this->methodBuilderMock->expects($this->once())
            ->method('populateWithArray')->with($output)->will($this->returnValue($this->methodBuilderMock));
        $this->methodBuilderMock->expects($this->once())->method('create');

        $this->service->getMethod($cartId);
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

        $this->assertNull($this->service->getMethod($cartId));
    }

    public function testGetListForVirtualCart()
    {
        $cartId = 834;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')->will($this->returnValue(true));

        $this->assertEquals([], $this->service->getList($cartId));
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

        $this->assertEquals([], $this->service->getList($cartId));
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

        $this->service->getList($cartId);
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
        $shippingRateMock = $this->getMock('\Magento\Sales\Model\Quote\Address\Rate', [], [], '', false);
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
        $this->assertEquals(['RateValue'], $this->service->getList($cartId));
    }
}
