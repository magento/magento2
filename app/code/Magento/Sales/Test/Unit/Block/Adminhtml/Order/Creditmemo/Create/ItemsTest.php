<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\Backend\Block\Template\Context;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemsTest extends TestCase
{
    /** @var Items */
    protected $items;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var StockItemInterface|MockObject */
    protected $stockItemMock;

    /** @var Registry|MockObject */
    protected $registryMock;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfig;

    /** @var MockObject */
    protected $stockConfiguration;

    /**
     * @var MockObject
     */
    protected $stockRegistry;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStockItem'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            Item::class,
            ['getManageStock']
        );

        $this->stockConfiguration = $this->createPartialMock(
            Configuration::class,
            ['canSubtractQty']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->registryMock = $this->createMock(Registry::class);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->items = $this->objectManagerHelper->getObject(
            Items::class,
            [
                'context' => $this->contextMock,
                'stockRegistry' => $this->stockRegistry,
                'stockConfiguration' => $this->stockConfiguration,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * @param bool $canReturnToStock
     * @param bool $manageStock
     * @param bool $result
     * @dataProvider canReturnItemsToStockDataProvider
     */
    public function testCanReturnItemsToStock($canReturnToStock, $manageStock, $result)
    {
        $productId = 7;
        $property = new \ReflectionProperty($this->items, '_canReturnToStock');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->items));
        $this->stockConfiguration->expects($this->once())
            ->method('canSubtractQty')
            ->willReturn($canReturnToStock);

        if ($canReturnToStock) {
            $orderItem = $this->createPartialMock(
                \Magento\Sales\Model\Order\Item::class,
                ['getProductId', 'getStore']
            );
            $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
            $store->expects($this->once())
                ->method('getWebsiteId')
                ->willReturn(10);
            $orderItem->expects($this->any())
                ->method('getStore')
                ->willReturn($store);
            $orderItem->expects($this->once())
                ->method('getProductId')
                ->willReturn($productId);

            $creditMemoItem = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)->addMethods(
                ['setCanReturnToStock']
            )
                ->onlyMethods(['getOrderItem'])
                ->disableOriginalConstructor()
                ->getMock();

            $creditMemo = $this->createMock(Creditmemo::class);
            $creditMemo->expects($this->once())
                ->method('getAllItems')
                ->willReturn([$creditMemoItem]);
            $creditMemoItem->expects($this->any())
                ->method('getOrderItem')
                ->willReturn($orderItem);

            $this->stockItemMock->expects($this->once())
                ->method('getManageStock')
                ->willReturn($manageStock);

            $creditMemoItem->expects($this->once())
                ->method('setCanReturnToStock')
                ->with($manageStock)->willReturnSelf();

            $order = $this->getMockBuilder(Order::class)
                ->addMethods(['setCanReturnToStock'])
                ->disableOriginalConstructor()
                ->getMock();
            $order->expects($this->once())
                ->method('setCanReturnToStock')
                ->with($manageStock)->willReturnSelf();
            $creditMemo->expects($this->once())
                ->method('getOrder')
                ->willReturn($order);

            $this->registryMock->expects($this->any())
                ->method('registry')
                ->with('current_creditmemo')
                ->willReturn($creditMemo);
        }

        $this->assertSame($result, $this->items->canReturnItemsToStock());
        $this->assertSame($result, $property->getValue($this->items));
        // lazy load test
        $this->assertSame($result, $this->items->canReturnItemsToStock());
    }

    /**
     * @return array
     */
    public static function canReturnItemsToStockDataProvider()
    {
        return [
            'cannot subtract by config' => [false, true, false],
            'manage stock is enabled' => [true, true, true],
            'manage stock is disabled' => [true, false, false],
        ];
    }
}
