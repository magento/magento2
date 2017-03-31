<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items */
    protected $items;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockConfiguration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(\Magento\Backend\Block\Template\Context::class, [], [], '', false);
        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->getMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getManageStock', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockConfiguration = $this->getMock(
            \Magento\CatalogInventory\Model\Configuration::class,
            ['__wakeup', 'canSubtractQty'],
            [],
            '',
            false
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->registryMock = $this->getMock(\Magento\Framework\Registry::class);
        $this->scopeConfig = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue($this->scopeConfig));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->items = $this->objectManagerHelper->getObject(
            \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items::class,
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
            $orderItem = $this->getMock(
                \Magento\Sales\Model\Order\Item::class,
                ['getProductId', '__wakeup', 'getStore'],
                [],
                '',
                false
            );
            $store = $this->getMock(\Magento\Store\Model\Store::class, ['getWebsiteId'], [], '', false);
            $store->expects($this->once())
                ->method('getWebsiteId')
                ->will($this->returnValue(10));
            $orderItem->expects($this->any())
                ->method('getStore')
                ->will($this->returnValue($store));
            $orderItem->expects($this->once())
                ->method('getProductId')
                ->will($this->returnValue($productId));

            $creditMemoItem = $this->getMock(
                \Magento\Sales\Model\Order\Creditmemo\Item::class,
                ['setCanReturnToStock', 'getOrderItem', '__wakeup'],
                [],
                '',
                false
            );

            $creditMemo = $this->getMock(\Magento\Sales\Model\Order\Creditmemo::class, [], [], '', false);
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

            $order = $this->getMock(
                \Magento\Sales\Model\Order::class,
                ['setCanReturnToStock', '__wakeup'],
                [],
                '',
                false
            );
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
