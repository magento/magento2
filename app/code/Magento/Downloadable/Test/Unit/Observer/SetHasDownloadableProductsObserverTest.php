<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Observer;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Downloadable\Observer\SetHasDownloadableProductsObserver;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetHasDownloadableProductsObserverTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var SetHasDownloadableProductsObserver
     */
    private $setHasDownloadableProductsObserver;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->orderMock = $this->createPartialMock(Order::class, ['getAllItems']);

        $this->checkoutSessionMock = $this->getMockBuilder(CheckoutSession::class)
            ->addMethods(['getHasDownloadableProducts', 'setHasDownloadableProducts'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->setHasDownloadableProductsObserver = $this->objectManager->getObject(
            SetHasDownloadableProductsObserver::class,
            [
                'checkoutSession' => $this->checkoutSessionMock
            ]
        );
    }

    /**
     * Test execute with session has downloadable products
     */
    public function testExecuteWithSessionHasDownloadableProducts()
    {
        $event = new DataObject(['item' => $this->orderMock]);
        $observer = new Observer(['event' => $event]);

        $this->checkoutSessionMock->method('getHasDownloadableProducts')->willReturn(true);
        $this->orderMock->method('getAllItems')->willReturn([]);

        $this->checkoutSessionMock->expects($this->never())
            ->method('setHasDownloadableProducts')->with(true);

        $this->setHasDownloadableProductsObserver->execute($observer);
    }

    /**
     * Test execute with session has no downloadable products with the data provider
     *
     * @dataProvider executeWithSessionNoDownloadableProductsDataProvider
     */
    public function testExecuteWithSessionNoDownloadableProducts($allItems, $expectedCall)
    {
        $event = new DataObject(['order' => $this->orderMock]);
        $observer = new Observer(['event' => $event]);

        $allOrderItemsMock = [];
        foreach ($allItems as $item) {
            $allOrderItemsMock[] = $this->createOrderItem(...$item);
        }

        $this->checkoutSessionMock->method('getHasDownloadableProducts')->willReturn(false);

        $this->orderMock->method('getAllItems')->willReturn($allOrderItemsMock);

        $this->checkoutSessionMock->expects($expectedCall)
            ->method('setHasDownloadableProducts')->with(true);

        $this->setHasDownloadableProductsObserver->execute($observer);
    }

    /**
     * Create Order Item Mock
     *
     * @param string $productType
     * @param string $realProductType
     * @param string $isDownloadable
     * @return Item|MockObject
     */
    private function createOrderItem(
        $productType = DownloadableProductType::TYPE_DOWNLOADABLE,
        $realProductType = DownloadableProductType::TYPE_DOWNLOADABLE,
        $isDownloadable = '1'
    ) {
        $item = $this->createPartialMock(
            Item::class,
            ['getProductType', 'getRealProductType', 'getProductOptionByCode']
        );

        $item->expects($this->any())
            ->method('getProductType')
            ->willReturn($productType);
        $item->expects($this->any())
            ->method('getRealProductType')
            ->willReturn($realProductType);
        $item->expects($this->any())
            ->method('getProductOptionByCode')
            ->with('is_downloadable')
            ->willReturn($isDownloadable);

        return $item;
    }

    /**
     * Data Provider for test execute with session has no downloadable product
     *
     * @return array
     */
    public function executeWithSessionNoDownloadableProductsDataProvider()
    {
        return [
            'Order has one item is downloadable product' => [
                [
                    [
                        DownloadableProductType::TYPE_DOWNLOADABLE,
                        DownloadableProductType::TYPE_DOWNLOADABLE,
                        '1'
                    ],
                    [
                        ProductType::TYPE_SIMPLE,
                        ProductType::TYPE_SIMPLE,
                        '1'
                    ]
                ],
                $this->once()
            ],
            'Order has all items are simple product' => [
                [
                    [
                        ProductType::TYPE_SIMPLE,
                        ProductType::TYPE_SIMPLE,
                        '0'
                    ],
                    [
                        ProductType::TYPE_SIMPLE,
                        ProductType::TYPE_SIMPLE,
                        '0'
                    ]
                ],
                $this->never()
            ],
        ];
    }
}
