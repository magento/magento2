<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Api\Data\TotalSegmentInterface;
use Magento\Quote\Api\Data\TotalsInterface as QuoteTotalsInterface;
use Magento\Quote\Api\Data\TotalsInterfaceFactory;
use Magento\Quote\Model\Cart\CartTotalRepository;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Model\Cart\TotalsConverter;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Cart totals object for class \Magento\Quote\Model\Cart\CartTotalRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTotalRepositoryTest extends TestCase
{
    private const STUB_CART_ID = 12;

    private const STUB_ITEMS_QTY = 100;

    private const STUB_CURRENCY_CODE = 'en_US';

    private const STUB_COUPON = 'coupon';

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * @var ItemConverter|MockObject
     */
    protected $converterMock;

    /**
     * @var CartTotalRepository
     */
    protected $model;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * @var TotalsInterfaceFactory|MockObject
     */
    private $totalsFactoryMock;

    /**
     * @var MockObject
     */
    protected $addressMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var CouponManagementInterface|MockObject
     */
    protected $couponServiceMock;

    /**
     * @var TotalsConverter|MockObject
     */
    protected $totalsConverterMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->totalsFactoryMock = $this->createPartialMock(
            TotalsInterfaceFactory::class,
            [
                'create'
            ]
        );
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getBaseCurrencyCode', 'getQuoteCurrencyCode'])
            ->onlyMethods(
                [
                    'isVirtual',
                    'getShippingAddress',
                    'getBillingAddress',
                    'getAllVisibleItems',
                    'getItemsQty',
                    'collectTotals'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->createMock(
            CartRepositoryInterface::class
        );
        $this->addressMock = $this->createPartialMock(
            Address::class,
            [
                'getData',
                'getTotals'
            ]
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(
            DataObjectHelper::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->converterMock = $this->createMock(
            ItemConverter::class
        );

        $this->couponServiceMock = $this->createMock(
            CouponManagementInterface::class
        );
        $this->totalsConverterMock = $this->createMock(
            TotalsConverter::class
        );

        $this->model = new CartTotalRepository(
            $this->totalsFactoryMock,
            $this->quoteRepositoryMock,
            $this->dataObjectHelperMock,
            $this->couponServiceMock,
            $this->totalsConverterMock,
            $this->converterMock
        );
    }

    /**
     * Test get cart total
     *
     * @param bool $isVirtual
     * @param string $getAddressType
     * @dataProvider getDataProvider
     *
     * @return void
     */
    public function testGetCartTotal($isVirtual, $getAddressType): void
    {
        $addressTotals = ['address' => 'totals'];
        $itemMock = $this->createMock(QuoteItem::class);
        $visibleItems = [
            11 => $itemMock,
        ];
        $itemArray = [
            'name' => 'item',
            'options' => [ 4 => ['label' => 'justLabel']],
        ];
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with(self::STUB_CART_ID)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')
            ->willReturn($isVirtual);
        $this->quoteMock->expects($this->exactly(2))
            ->method($getAddressType)
            ->willReturn($this->addressMock);
        $this->quoteMock->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn($visibleItems);
        $this->quoteMock->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn(self::STUB_CURRENCY_CODE);
        $this->quoteMock->expects($this->once())
            ->method('getQuoteCurrencyCode')
            ->willReturn(self::STUB_CURRENCY_CODE);
        $this->quoteMock->expects($this->once())
            ->method('getItemsQty')
            ->willReturn(self::STUB_ITEMS_QTY);
        $this->addressMock->expects($this->any())
            ->method('getData')
            ->willReturn($addressTotals);
        $this->addressMock->expects($this->once())
            ->method('getTotals')
            ->willReturn($addressTotals);

        $totalsMock = $this->getMockForAbstractClass(QuoteTotalsInterface::class);
        $this->totalsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($totalsMock);
        $this->dataObjectHelperMock->expects($this->once())->method('populateWithArray');
        $this->converterMock->expects($this->once())
            ->method('modelToDataObject')
            ->with($itemMock)
            ->willReturn($itemArray);

        $totalSegmentsMock = $this->getMockForAbstractClass(TotalSegmentInterface::class);
        $this->totalsConverterMock->expects($this->once())
            ->method('process')
            ->with($addressTotals)
            ->willReturn($totalSegmentsMock);

        $this->couponServiceMock
            ->expects($this->once())
            ->method('get')
            ->with(self::STUB_CART_ID)
            ->willReturn(self::STUB_COUPON);

        $totalsMock->expects($this->once())
            ->method('setItems')
            ->with([11 => $itemArray])
            ->willReturnSelf();
        $totalsMock->expects($this->once())
            ->method('setTotalSegments')
            ->with($totalSegmentsMock)
            ->willReturnSelf();
        $totalsMock->expects($this->once())
            ->method('setCouponCode')
            ->with(self::STUB_COUPON)
            ->willReturnSelf();
        $totalsMock->expects($this->never())
            ->method('setGrandTotal')
            ->willReturnSelf();
        $totalsMock->expects($this->once())
            ->method('setItemsQty')
            ->with(self::STUB_ITEMS_QTY)
            ->willReturnSelf();
        $totalsMock->expects($this->once())
            ->method('setBaseCurrencyCode')
            ->with(self::STUB_CURRENCY_CODE)
            ->willReturnSelf();
        $totalsMock->expects($this->once())
            ->method('setQuoteCurrencyCode')
            ->with(self::STUB_CURRENCY_CODE)
            ->willReturnSelf();

        $this->assertEquals($totalsMock, $this->model->get(self::STUB_CART_ID));
    }

    /**
     * Provide data for test different cases
     *
     * @param void
     * @return array
     */
    public static function getDataProvider(): array
    {
        return [
            'Virtual Quote' => [
                'isVirtual' => true,
                'getAddressType' => 'getBillingAddress'
            ],
            'Non-virtual Quote' => [
                'isVirtual' => false,
                'getAddressType' => 'getShippingAddress'
            ]
        ];
    }
}
