<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            Item::class,
            ['getManageStock', '__wakeup']
        );

        $this->stockConfiguration = $this->createPartialMock(
            Configuration::class,
            ['__wakeup', 'canSubtractQty']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->registryMock = $this->createMock(Registry::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue($this->scopeConfig));

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
            ->will($this->returnValue($canReturnToStock));

        if ($canReturnToStock) {
            $orderItem = $this->createPartialMock(
                \Magento\Sales\Model\Order\Item::class,
                ['getProductId', '__wakeup', 'getStore']
            );
            $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
            $store->expects($this->once())
                ->method('getWebsiteId')
                ->will($this->returnValue(10));
            $orderItem->expects($this->any())
                ->method('getStore')
                ->will($this->returnValue($store));
            $orderItem->expects($this->once())
                ->method('getProductId')
                ->will($this->returnValue($productId));

            $creditMemoItem = $this->createPartialMock(
                \Magento\Sales\Model\Order\Creditmemo\Item::class,
                ['setCanReturnToStock', 'getOrderItem', '__wakeup']
            );

            $creditMemo = $this->createMock(Creditmemo::class);
            $creditMemo->expects($this->once())
                ->method('getAllItems')
                ->will($this->returnValue([$creditMemoItem]));
            $creditMemoItem->expects($this->any())
                ->method('getOrderItem')
                ->will($this->returnValue($orderItem));

            $this->stockItemMock->expects($this->once())
                ->method('getManageStock')
                ->will($this->returnValue($manageStock));

            $creditMemoItem->expects($this->once())
                ->method('setCanReturnToStock')
                ->with($this->equalTo($manageStock))
                ->will($this->returnSelf());

            $order = $this->createPartialMock(Order::class, ['setCanReturnToStock', '__wakeup']);
            $order->expects($this->once())
                ->method('setCanReturnToStock')
                ->with($this->equalTo($manageStock))
                ->will($this->returnSelf());
            $creditMemo->expects($this->once())
                ->method('getOrder')
                ->will($this->returnValue($order));

            $this->registryMock->expects($this->any())
                ->method('registry')
                ->with('current_creditmemo')
                ->will($this->returnValue($creditMemo));
        }

        $this->assertSame($result, $this->items->canReturnItemsToStock());
        $this->assertSame($result, $property->getValue($this->items));
        // lazy load test
        $this->assertSame($result, $this->items->canReturnItemsToStock());
    }

    /**
     * @return array
     */
    public function canReturnItemsToStockDataProvider()
    {
        return [
            'cannot subtract by config' => [false, true, false],
            'manage stock is enabled' => [true, true, true],
            'manage stock is disabled' => [true, false, false],
        ];
    }
}
