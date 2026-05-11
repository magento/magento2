<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Multishipping\Plugin\MultishippingQuoteRepository;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingProcessor;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use Magento\Quote\Model\ShippingAssignmentFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test case for MultishippingQuoteRepository plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MultishippingQuoteRepositoryTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $cartMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var QuoteItem|MockObject
     */
    private $quoteItemMock;

    /**
     * @var ShippingAssignmentFactory|MockObject
     */
    private $shippingAssignmentFactoryMock;

    /**
     * @var ShippingProcessor|MockObject
     */
    private $shippingProcessorMock;

    /**
     * @var MultishippingQuoteRepository
     */
    private $multishippingQuoteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->cartMock = $this->createMock(CartRepositoryInterface::class);
        $this->quoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            [
                'hasVirtualItems',
                'getAllShippingAddresses',
                'getAllAddresses',
                'getBillingAddress',
                'getPayment',
                'getIsMultiShipping',
                'reserveOrderId',
                'setIsActive',
                'setItems',
                'getItems',
                'getItemById',
                'getExtensionAttributes'
            ]
        );
        $this->quoteItemMock = $this->createMock(QuoteItem::class);
        $this->shippingAssignmentFactoryMock = $this->createMock(ShippingAssignmentFactory::class);
        $this->shippingProcessorMock = $this->createMock(ShippingProcessor::class);
        $this->multishippingQuoteRepository = new MultishippingQuoteRepository(
            $this->shippingAssignmentFactoryMock,
            $this->shippingProcessorMock
        );
    }

    /**
     * Test afterGet plugin and check the quote has items or null
     *
     * @param bool $isMultiShippingMode
     * @param array $productData
     * @return void
     */
    #[DataProvider('pluginForAfterGetMultiShippingModeDataProvider')]
    public function testPluginAfterGetWithMultiShippingMode(bool $isMultiShippingMode, array $productData): void
    {
        $simpleProductTypeMock = $this->getMockBuilder(Simple::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOrderOptions'])
            ->getMock();
        $productMock = $this->getProductMock($simpleProductTypeMock);
        $this->getQuoteItemMock($productData['productType'], $productMock);
        $quoteAddressItemMock = $this->getQuoteAddressItemMock(
            $productData['productType'],
            $productData['productOptions']
        );
        list($shippingAddressMock, $billingAddressMock) =
            $this->getQuoteAddressesMock($quoteAddressItemMock);
        $this->setQuoteMockData($productData['paymentProviderCode'], $shippingAddressMock, $billingAddressMock);
        $this->quoteItemMock->method('setQuote')->with($this->quoteMock)->willReturnSelf();
        $this->quoteItemMock->method('getQuote')->willReturn($this->quoteMock);
        $extensionAttributesMock = $this->createPartialMockWithReflection(
            CartExtensionInterface::class,
            ['getShippingAssignments', 'setShippingAssignments']
        );
        $this->quoteMock->expects($this->any())
            ->method('getIsMultiShipping')
            ->willReturn($isMultiShippingMode);
        $this->quoteMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesMock);
        $extensionAttributesMock->expects($this->any())
            ->method('getShippingAssignments')
            ->willReturn($this->shippingAssignmentFactoryMock);
        
        $shippingAssignmentMock = $this->createMock(ShippingAssignment::class);
        $this->shippingAssignmentFactoryMock->method('create')->willReturn($shippingAssignmentMock);
        
        $shippingMock = $this->createMock(Shipping::class);
        $this->shippingProcessorMock->method('create')->willReturn($shippingMock);

        $quote = $this->multishippingQuoteRepository->afterGet($this->cartMock, $this->quoteMock);
        $this->assertNotEmpty($quote);
        $this->assertEquals(1, count($quote->getItems()));
        $this->assertNotEmpty(current($quote->getItems()));
    }

    /**
     * Return Product Mock.
     *
     * @param Simple|MockObject $simpleProductTypeMock
     * @return MockObject
     */
    private function getProductMock($simpleProductTypeMock): MockObject
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTypeInstance'])
            ->getMock();
        $productMock->method('getTypeInstance')->willReturn($simpleProductTypeMock);

        return $productMock;
    }

    /**
     * Return Quote Item Mock.
     *
     * @param string $productType
     * @param Product|MockObject $productMock
     * @return void
     */
    private function getQuoteItemMock(string $productType, Product|MockObject $productMock): void
    {
        $this->quoteItemMock->method('getProductType')->willReturn($productType);
        $this->quoteItemMock->method('getProduct')->willReturn($productMock);
        $this->quoteItemMock->method('getQty')->willReturn(1);
        $this->quoteItemMock->method('getPrice')->willReturn(10);
        $this->quoteItemMock->method('getQuote')->willReturn($this->quoteMock);
    }

    /**
     * Return Quote Address Item Mock
     *
     * @param string $productType
     * @param array $productOptions
     * @return MockObject
     */
    private function getQuoteAddressItemMock(string $productType, array $productOptions): MockObject
    {
        $quoteAddressItemMock = $this->createPartialMockWithReflection(
            Item::class,
            ['getQuoteItem', 'setProductType', 'setProductOptions', 'getParentItem']
        );
        $quoteAddressItemMock->method('getQuoteItem')->willReturn($this->quoteItemMock);
        $quoteAddressItemMock->method('setProductType')->with($productType)->willReturnSelf();
        $quoteAddressItemMock->method('setProductOptions')->willReturn($productOptions);
        $quoteAddressItemMock->method('getParentItem')->willReturn(false);

        return $quoteAddressItemMock;
    }

    /**
     * Return Quote Addresses Mock
     * @param Item|MockObject $quoteAddressItemMock
     * @return array
     */
    private function getQuoteAddressesMock(Item|MockObject $quoteAddressItemMock): array
    {
        $shippingAddressMock = $this->createPartialMockWithReflection(
            Address::class,
            [
                'getAddressType',
                'getGrandTotal',
                'validate',
                'getShippingMethod',
                'getShippingRateByCode',
                'getCountryId',
                'getAllItems',
                'getItemsCollection',
            ]
        );
        $shippingAddressMock->method('validate')->willReturn(true);
        $shippingAddressMock->method('getAllItems')->willReturn([$quoteAddressItemMock]);
        $shippingAddressMock->method('getItemsCollection')->willReturn([$quoteAddressItemMock]);
        $shippingAddressMock->method('getAddressType')->willReturn('shipping');

        $shippingRateMock = $this->createPartialMockWithReflection(Rate::class, ['getPrice']);
        $shippingAddressMock->method('getShippingRateByCode')->willReturn($shippingRateMock);

        $billingAddressMock = $this->createMock(Address::class);
        $billingAddressMock->method('validate')->willReturn(true);
        $billingAddressMock->method('getItemsCollection')->willReturn([]);

        return [$shippingAddressMock, $billingAddressMock];
    }

    /**
     * Set data for Quote Mock.
     *
     * @param string $paymentProviderCode
     * @param Address|MockObject $shippingAddressMock
     * @param Address|MockObject $billingAddressMock
     * @return void
     */
    private function setQuoteMockData(
        string $paymentProviderCode,
        Address|MockObject $shippingAddressMock,
        Address|MockObject $billingAddressMock
    ): void {
        $paymentMock = $this->getPaymentMock($paymentProviderCode);
        $this->quoteMock->method('getPayment')
            ->willReturn($paymentMock);
        $this->quoteMock->method('getAllShippingAddresses')
            ->willReturn([$shippingAddressMock]);
        $this->quoteMock->method('getAllAddresses')
            ->willReturn([$shippingAddressMock, $billingAddressMock]);
        $this->quoteMock->method('getBillingAddress')
            ->willReturn($billingAddressMock);
        $this->quoteMock->method('hasVirtualItems')
            ->willReturn(false);
        $this->quoteMock->expects($this->any())->method('reserveOrderId')->willReturnSelf();
        $this->quoteMock->method('setIsActive')->with(false)->willReturnSelf();
        $this->quoteMock->method('setItems')->with([$this->quoteItemMock])->willReturnSelf();
        $this->quoteMock->method('getItems')->willReturn([$this->quoteItemMock]);
        $this->quoteMock->method('getItemById')->willReturn($this->quoteItemMock);
    }

    /**
     * Return Payment Mock.
     *
     * @param string $paymentProviderCode
     * @return MockObject
     */
    private function getPaymentMock(string $paymentProviderCode): MockObject
    {
        $abstractMethod = $this->getMockBuilder(AbstractMethod::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAvailable'])
            ->getMock();
        $abstractMethod->method('isAvailable')->willReturn(true);

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethodInstance', 'getMethod'])
            ->getMock();
        $paymentMock->method('getMethodInstance')->willReturn($abstractMethod);
        $paymentMock->method('getMethod')->willReturn($paymentProviderCode);

        return $paymentMock;
    }

    /**
     * DataProvider for pluginForAfterGetMultiShippingModeDataProvider().
     *
     * @return array
     */
    public static function pluginForAfterGetMultiShippingModeDataProvider(): array
    {
        $productData = [
            'productType' => Type::TYPE_SIMPLE,
            'paymentProviderCode' => 'checkmo',
            'productOptions' => [
                'info_buyRequest' => [
                    'product' => '1',
                    'qty' => 1,
                ],
            ]
        ];
        return [
            'test case for multi shipping quote' => [true, $productData],
            'test case for single shipping quote' => [false, $productData]
        ];
    }
}
