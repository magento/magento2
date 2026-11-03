<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Handler\State;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class StateTest extends TestCase
{
    use MockCreationTrait;

    /** @var State */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new State();
    }

    public function testSetsProcessingWhenNewAndInProcess(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getStateDefaultStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->method('getState')->willReturn(Order::STATE_NEW);
        $order->method('getIsInProcess')->willReturn(true);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();

        $config->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn('processing');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('processing')
            ->willReturnSelf();

        $order->method('getConfig')->willReturn($config);

        // Ensure early-return guard does not trigger
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);

        // Prevent subsequent COMPLETE/CLOSED transitions after PROCESSING
        $order->method('canShip')->willReturn(true);
        $order->method('getAllItems')->willReturn([
            $this->createOrderItemStub(1, 0, 0, 0) // openQty > 0 -> not fulfilled
        ]);

        $this->subject->check($order);
    }

    public function testChildOfBundleShippedTogetherIsSkippedAndParentDeterminesFulfillment(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getStateDefaultStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);
        $order->method('canCreditmemo')->willReturn(false);
        $order->method('getIsNotVirtual')->willReturn(true);
        $order->method('canShip')->willReturn(true);

        $parentItem = $this->createBundleParentShippedTogetherFulfilled();
        $childItem = $this->createChildItemForParent($parentItem, 0);
        $order->method('getAllItems')->willReturn([$childItem, $parentItem]);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_CLOSED)
            ->willReturnSelf();

        $config->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_CLOSED)
            ->willReturn('closed');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('closed')
            ->willReturnSelf();

        $order->method('getConfig')->willReturn($config);

        $this->subject->check($order);
    }

    public function testVirtualItemIsSkippedInFulfillment(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getStateDefaultStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);

        // Avoid early return
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);

        // Closed state via areAllItemsFulfilled (canShip = true)
        $order->method('canCreditmemo')->willReturn(false);
        $order->method('getIsNotVirtual')->willReturn(true);
        $order->method('canShip')->willReturn(true);

        // Virtual item with open qty should be skipped
        $virtualItem = new class {
            public function getIsVirtual()
            {
                return true;
            }
            public function getLockedDoShip()
            {
                return false;
            }
            public function getParentItem()
            {
                return null;
            }
            public function getProductType()
            {
                return 'virtual';
            }
            public function getProduct()
            {
                return null;
            }
            public function getQtyOrdered()
            {
                return 1;
            }
            public function getQtyCanceled()
            {
                return 0;
            }
            public function getQtyShipped()
            {
                return 0;
            }
            public function getQtyRefunded()
            {
                return 0;
            }
        };

        // Regular item fully fulfilled determines overall fulfillment
        $fulfilledItem = $this->createOrderItemStub(1, 0, 1, 0);

        $order->method('getAllItems')->willReturn([$virtualItem, $fulfilledItem]);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_CLOSED)
            ->willReturnSelf();

        $config->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_CLOSED)
            ->willReturn('closed');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('closed')
            ->willReturnSelf();

        $order->method('getConfig')->willReturn($config);

        $this->subject->check($order);
    }

    public function testLockedDoShipItemIsSkippedInFulfillment(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getStateDefaultStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);

        // Avoid early return
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);

        // Closed state via areAllItemsFulfilled (canShip = true)
        $order->method('canCreditmemo')->willReturn(false);
        $order->method('getIsNotVirtual')->willReturn(true);
        $order->method('canShip')->willReturn(true);

        // Locked-do-ship item with open qty should be skipped
        $lockedItem = new class {
            public function getIsVirtual()
            {
                return false;
            }
            public function getLockedDoShip()
            {
                return true;
            }
            public function getParentItem()
            {
                return null;
            }
            public function getProductType()
            {
                return 'simple';
            }
            public function getProduct()
            {
                return null;
            }
            public function getQtyOrdered()
            {
                return 2;
            }
            public function getQtyCanceled()
            {
                return 0;
            }
            public function getQtyShipped()
            {
                return 0;
            }
            public function getQtyRefunded()
            {
                return 0;
            }
        };

        // Regular item fully fulfilled determines overall fulfillment
        $fulfilledItem = $this->createOrderItemStub(1, 0, 1, 0);

        $order->method('getAllItems')->willReturn([$lockedItem, $fulfilledItem]);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_CLOSED)
            ->willReturnSelf();

        $config->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_CLOSED)
            ->willReturn('closed');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('closed')
            ->willReturnSelf();

        $order->method('getConfig')->willReturn($config);

        $this->subject->check($order);
    }

    public function testSubjectChoosesParentWhenSecondEvaluationMatchesBundleShippedTogether(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getStateDefaultStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);

        // Avoid early return
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);

        // Closed state via areAllItemsFulfilled
        $order->method('canCreditmemo')->willReturn(false);
        $order->method('getIsNotVirtual')->willReturn(true);
        $order->method('canShip')->willReturn(true);

        $parentWithSwitchingBehavior = $this->createSwitchingParentForBundleSelection();
        $childItem = $this->createChildItemForParent($parentWithSwitchingBehavior, 0);
        $order->method('getAllItems')->willReturn([$childItem]);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_CLOSED)
            ->willReturnSelf();

        $config->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_CLOSED)
            ->willReturn('closed');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('closed')
            ->willReturnSelf();

        $order->method('getConfig')->willReturn($config);

        $this->subject->check($order);
    }

    private function createBundleParentShippedTogetherFulfilled(): object
    {
        $bundleProduct = new class {
            public function getShipmentType()
            {
                return AbstractType::SHIPMENT_TOGETHER;
            }
        };

        return new class($bundleProduct) {
            /** @var object */
            private $bundleProduct;
            public function __construct($bundleProduct)
            {
                $this->bundleProduct = $bundleProduct;
            }
            public function getIsVirtual()
            {
                return false;
            }
            public function getLockedDoShip()
            {
                return false;
            }
            public function getParentItem()
            {
                return null;
            }
            public function getProductType()
            {
                return Type::TYPE_BUNDLE;
            }
            public function getProduct()
            {
                return $this->bundleProduct;
            }
            public function getQtyOrdered()
            {
                return 1;
            }
            public function getQtyCanceled()
            {
                return 0;
            }
            public function getQtyShipped()
            {
                return 1;
            }
            public function getQtyRefunded()
            {
                return 0;
            }
        };
    }

    private function createChildItemForParent(object $parent, int $shipped): object
    {
        $child = new class {
            /** @var object */
            public $parent;
            /** @var int */
            public $shipped = 0;
            public function getIsVirtual()
            {
                return false;
            }
            public function getLockedDoShip()
            {
                return false;
            }
            public function getParentItem()
            {
                return $this->parent;
            }
            public function getProductType()
            {
                return 'simple';
            }
            public function getProduct()
            {
                return null;
            }
            public function getQtyOrdered()
            {
                return 1;
            }
            public function getQtyCanceled()
            {
                return 0;
            }
            public function getQtyShipped()
            {
                return $this->shipped;
            }
            public function getQtyRefunded()
            {
                return 0;
            }
        };
        $child->parent = $parent;
        $child->shipped = $shipped;
        return $child;
    }

    private function createSwitchingParentForBundleSelection(): object
    {
        return new class {
            /** @var int */
            private $typeCall = 0;
            /** @var int */
            private $productCall = 0;
            public function getIsVirtual()
            {
                return false;
            }
            public function getLockedDoShip()
            {
                return false;
            }
            public function getParentItem()
            {
                return null;
            }
            public function getProductType()
            {
                $this->typeCall++;
                return $this->typeCall === 1 ? 'simple' : Type::TYPE_BUNDLE;
            }
            public function getProduct()
            {
                $this->productCall++;
                $callIndex = $this->productCall;
                return new class($callIndex) {
                    /** @var int */
                    private $callIndex;
                    public function __construct($callIndex)
                    {
                        $this->callIndex = $callIndex;
                    }
                    public function getShipmentType()
                    {
                        return $this->callIndex === 1
                            ? AbstractType::SHIPMENT_SEPARATELY
                            : AbstractType::SHIPMENT_TOGETHER;
                    }
                };
            }
            public function getQtyOrdered()
            {
                return 1;
            }
            public function getQtyCanceled()
            {
                return 0;
            }
            public function getQtyShipped()
            {
                return 1;
            }
            public function getQtyRefunded()
            {
                return 0;
            }
        };
    }

    public function testEarlyReturnWhenOpenInvoiceAndTotalDue(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getTotalDue')->willReturn(100);

        $openInvoice = $this->getMockBuilder(Invoice::class)
            ->onlyMethods(['getState'])
            ->disableOriginalConstructor()
            ->getMock();
        $openInvoice->method('getState')->willReturn(Invoice::STATE_OPEN);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([$openInvoice]));

        $order->expects($this->never())->method('setState');

        $this->subject->check($order);
    }

    public function testSetsCompleteWhenProcessingAndCannotShip(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getStateDefaultStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);
        $order->method('canShip')->willReturn(false);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_COMPLETE)
            ->willReturnSelf();

        $config->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_COMPLETE)
            ->willReturn('complete');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('complete')
            ->willReturnSelf();

        $order->method('getConfig')->willReturn($config);

        $this->subject->check($order);
    }

    public function testSetsClosedWhenProcessingNoCreditmemoAllFulfilledAndNotVirtual(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getStateDefaultStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);

        // Make closed condition true via all-items-fulfilled path
        $order->method('canShip')->willReturn(true);
        $order->method('canCreditmemo')->willReturn(false);
        $order->method('getIsNotVirtual')->willReturn(true);
        $order->method('getAllItems')->willReturn([
            $this->createOrderItemStub(2, 0, 2, 0)
        ]);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_CLOSED)
            ->willReturnSelf();

        $config->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_CLOSED)
            ->willReturn('closed');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('closed')
            ->willReturnSelf();

        $order->method('getConfig')->willReturn($config);

        $this->subject->check($order);
    }

    #[DataProvider('partiallyShippedDecimalQtyProvider')]
    public function testOrderStaysProcessingWhenDecimalQtyIsNotFullyShipped(float $ordered, float $shipped): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);
        $order->method('canShip')->willReturn(true);
        $order->method('canCreditmemo')->willReturn(false);
        $order->method('getIsNotVirtual')->willReturn(true);
        $order->method('getAllItems')->willReturn([
            $this->createOrderItemStub($ordered, 0, $shipped, 0)
        ]);

        $order->expects($this->never())->method('setState');

        $this->subject->check($order);
    }

    /**
     * @return array
     */
    public static function partiallyShippedDecimalQtyProvider(): array
    {
        return [
            'nothing shipped, qty below 1' => [0.5, 0.0],
            'fraction left after whole units shipped' => [1.5, 1.0],
        ];
    }

    public function testSetsCompleteWhenDecimalQtyIsFullyShipped(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getStateDefaultStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);
        $order->method('canShip')->willReturn(true);
        $order->method('canCreditmemo')->willReturn(true);
        // 0.9 - 0.3 - 0.6 is slightly above 0 in floating point arithmetic
        $order->method('getAllItems')->willReturn([
            $this->createOrderItemStub(0.9, 0.3, 0.6, 0)
        ]);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_COMPLETE)
            ->willReturnSelf();

        $config->method('getStateDefaultStatus')->with(Order::STATE_COMPLETE)->willReturn('complete');
        $order->method('setStatus')->with('complete')->willReturnSelf();
        $order->method('getConfig')->willReturn($config);

        $this->subject->check($order);
    }

    public function testSetsClosedForVirtualOrderWithClosedStatus(): void
    {
        /** @var Order|MockObject $order */
        $order = $this->createOrderMock();

        $config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getStateDefaultStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('getIsInProcess')->willReturn(false);

        // Avoid early return
        $order->method('isCanceled')->willReturn(false);
        $order->method('canUnhold')->willReturn(false);
        $order->method('canInvoice')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn($this->createInvoiceCollection([]));
        $order->method('getTotalDue')->willReturn(0);

        // Trigger virtual closed path
        $order->method('getIsVirtual')->willReturn(true);
        $order->method('getStatus')->willReturn(Order::STATE_CLOSED);
        $order->method('getIsNotVirtual')->willReturn(false);
        $order->method('canShip')->willReturn(true);
        // Ensure first closed-condition short-circuits and does not call areAllItemsFulfilled
        $order->method('canCreditmemo')->willReturn(true);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_CLOSED)
            ->willReturnSelf();

        $config->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_CLOSED)
            ->willReturn('closed');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('closed')
            ->willReturnSelf();

        $order->method('getConfig')->willReturn($config);

        $this->subject->check($order);
    }

    /**
     * Create an Order mock with stubbable methods used by State::check
     */
    private function createOrderMock(): Order|MockObject
    {
        return $this->createPartialMockWithReflection(
            Order::class,
            [
                'getState', 'setState', 'setStatus', 'getConfig', 'isCanceled', 'canUnhold',
                'canInvoice', 'getInvoiceCollection', 'getTotalDue', 'canShip', 'canCreditmemo',
                'getIsNotVirtual', 'getAllItems', 'getIsVirtual', 'getStatus', 'getIsInProcess'
            ]
        );
    }

    /**
     * Create a simple object that mimics an invoice collection with getItems().
     *
     * @param array $invoices
     * @return object
     */
    private function createInvoiceCollection(array $invoices): object
    {
        return new class($invoices) {
            /** @var array */
            private $invoices;

            public function __construct(array $invoices)
            {
                $this->invoices = $invoices;
            }

            public function getItems(): array
            {
                return $this->invoices;
            }
        };
    }

    /**
     * Create a minimal order item stub that exposes the quantities used by State::areAllItemsFulfilled().
     */
    private function createOrderItemStub(float $ordered, float $canceled, float $shipped, float $refunded): object
    {
        return new class($ordered, $canceled, $shipped, $refunded) {
            /** @var float */
            private $ordered;
            /** @var float */
            private $canceled;
            /** @var float */
            private $shipped;
            /** @var float */
            private $refunded;

            public function __construct(float $ordered, float $canceled, float $shipped, float $refunded)
            {
                $this->ordered = $ordered;
                $this->canceled = $canceled;
                $this->shipped = $shipped;
                $this->refunded = $refunded;
            }

            public function getIsVirtual(): bool
            {
                return false;
            }
            public function getLockedDoShip(): bool
            {
                return false;
            }
            public function getParentItem()
            {
                return null;
            }
            public function getQtyOrdered(): float
            {
                return $this->ordered;
            }
            public function getQtyCanceled(): float
            {
                return $this->canceled;
            }
            public function getQtyShipped(): float
            {
                return $this->shipped;
            }
            public function getQtyRefunded(): float
            {
                return $this->refunded;
            }
        };
    }
}
