<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\SalesRule\Observer\ProcessOrderCreationDataObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *  Test case for process order for resetting shipping flag.
 */
class ProcessOrderCreationDataObserverTest extends TestCase
{
    /**
     * @var MockObject|Observer
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * @var Address|MockObject
     */
    private $shippingAddressMock;

    /**
     * @var Create|MockObject
     */
    private $orderCreateModelMock;

    /**
     * @var ProcessOrderCreationDataObserver|MockObject
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->observerMock = $this->createMock(Observer::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['isVirtual', 'getShippingAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getRequest', 'getOrderCreateModel', 'getShippingMethod'])
            ->getMock();
        $this->shippingAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['setShippingMethod'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderCreateModelMock = $this->getMockBuilder(Create::class)
            ->onlyMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->model = new ProcessOrderCreationDataObserver();
    }

    /**
     * Test case for processOrderCreationDataObserver::execute
     *
     * @param bool $isVirtualQuote
     * @param array $requestArr
     * @param string|null $quoteShippingMethod
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        bool $isVirtualQuote,
        array $requestArr,
        ?string $quoteShippingMethod = null,
    ): void {
        $this->observerMock
            ->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($requestArr);
        $this->eventMock
            ->expects($this->any())
            ->method('getOrderCreateModel')
            ->willReturn($this->orderCreateModelMock);
        $this->eventMock
            ->expects($this->any())
            ->method('getShippingMethod')
            ->willReturn($quoteShippingMethod);
        $this->orderCreateModelMock
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock
            ->expects($this->any())
            ->method('isVirtual')
            ->willReturn($isVirtualQuote);
        $this->quoteMock
            ->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock
            ->expects($this->any())
            ->method('setShippingMethod')
            ->with($quoteShippingMethod)
            ->willReturn(true);
        $this->model->execute($this->observerMock);
    }

    /**
     * Data provider for testExecute
     *
     * @return array[]
     */
    public static function executeDataProvider(): array
    {
        return [
            [
                'isVirtualQuote' => false,
                'requestArr' =>
                    [
                        'order' => ['coupon' => 'coupon_code'],
                        'reset_shipping' => true,
                        'collect_shipping_rates' => true
                    ],
                'quoteShippingMethod' => 'flatrate_flatrate',
            ],
            [
                'isVirtualQuote' => true,
                'requestArr' =>
                    [
                        'order' => ['coupon' => 'coupon_code'],
                        'reset_shipping' => false
                    ],
                'quoteShippingMethod' => 'freeshipping_freeshipping',
            ],
            [
                'isVirtualQuote' => false,
                'requestArr' =>
                    [
                        'order' => ['coupon' => ''],
                        'collect_shipping_rates' => true
                    ],
                'quoteShippingMethod' => null,
            ],
            [
                'isVirtualQuote' => false,
                'requestArr' =>
                    [
                        'order' => ['coupon' => 'coupon_code'],
                        'reset_shipping' => false,
                        'collect_shipping_rates' => true
                    ],
                'quoteShippingMethod' => 'flatrate_flatrate'
            ]
        ];
    }
}
