<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Items;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AbstractItemsTest
 * @package Magento\Sales\Block\Adminhtml\Items
 * TODO refactor me PLEASE
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractItemsTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistry;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getManageStock', '__wakeup']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
    }

    public function testGetItemRenderer()
    {
        $layout = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock', 'getGroupChildNames']
        );
        $layout->expects($this->any())
            ->method('getChildName')
            ->with(null, 'some-type')
            ->willReturn('column_block-name');
        $layout->expects($this->any())
            ->method('getGroupChildNames')
            ->with(null, 'column')
            ->willReturn(['column_block-name']);

        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer $renderer */
        $renderer = $this->objectManagerHelper
            ->getObject(\Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer::class);
        $renderer->setLayout($layout);

        $layout->expects($this->any())
            ->method('getBlock')
            ->with('column_block-name')
            ->willReturn($renderer);

        /** @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems $block */
        $block = $this->objectManagerHelper->getObject(\Magento\Sales\Block\Adminhtml\Items\AbstractItems::class);
        $block->setLayout($layout);

        $this->assertSame($renderer, $block->getItemRenderer('some-type'));
        $this->assertSame($renderer, $renderer->getColumnRenderer('block-name'));
    }

    /**
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Renderer for type "some-type" does not exist.');

        $renderer = $this->createMock(\stdClass::class);
        $layout = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock', '__wakeup']
        );
        $layout->expects($this->at(0))
            ->method('getChildName')
            ->with(null, 'some-type')
            ->willReturn('some-block-name');
        $layout->expects($this->at(1))
            ->method('getBlock')
            ->with('some-block-name')
            ->willReturn($renderer);

        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $block = $this->objectManagerHelper->getObject(
            \Magento\Sales\Block\Adminhtml\Items\AbstractItems::class,
            [
                'context' => $this->objectManagerHelper->getObject(
                    \Magento\Backend\Block\Template\Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $block->getItemRenderer('some-type');
    }

    /**
     * @param bool $canReturnToStock
     * @param array $itemConfig
     * @param bool $result
     * @dataProvider canReturnItemToStockDataProvider
     */
    public function testCanReturnItemToStock($canReturnToStock, $itemConfig, $result)
    {
        $productId = isset($itemConfig['product_id']) ? $itemConfig['product_id'] : null;
        $manageStock = isset($itemConfig['manage_stock']) ? $itemConfig['manage_stock'] : null;
        $item = $this->createPartialMock(
            \Magento\Sales\Model\Order\Creditmemo\Item::class,
            ['hasCanReturnToStock', 'getOrderItem', 'setCanReturnToStock', 'getCanReturnToStock', '__wakeup']
        );
        $dependencies = $this->prepareServiceMockDependency(
            $item,
            $canReturnToStock,
            $productId,
            $manageStock,
            $itemConfig
        );

        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $block = $this->objectManagerHelper->getObject(
            \Magento\Sales\Block\Adminhtml\Items\AbstractItems::class,
            $dependencies
        );
        $this->assertSame($result, $block->canReturnItemToStock($item));
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $item
     * @param bool $canReturnToStock
     * @param int|null $productId
     * @param bool $manageStock
     * @param array $itemConfig
     * @return array
     */
    protected function prepareServiceMockDependency($item, $canReturnToStock, $productId, $manageStock, $itemConfig)
    {
        $dependencies = [];

        $this->stockItemMock->expects($this->any())
            ->method('getManageStock')
            ->willReturn($manageStock);
        $dependencies['stockRegistry'] = $this->stockRegistry;

        $item->expects($this->once())
            ->method('hasCanReturnToStock')
            ->willReturn($itemConfig['has_can_return_to_stock']);
        if (!$itemConfig['has_can_return_to_stock']) {
            $orderItem = $this->createPartialMock(
                \Magento\Sales\Model\Order\Item::class,
                ['getProductId', '__wakeup', 'getStore']
            );

            $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
            $store->expects($this->once())
                ->method('getWebsiteId')
                ->willReturn(10);
            $orderItem->expects($this->once())
                ->method('getStore')
                ->willReturn($store);

            $orderItem->expects($this->once())
                ->method('getProductId')
                ->willReturn($productId);
            $item->expects($this->any())
                ->method('getOrderItem')
                ->willReturn($orderItem);
            if ($productId && $manageStock) {
                $canReturn = true;
            } else {
                $canReturn = false;
            }
            $item->expects($this->once())
                ->method('setCanReturnToStock')
                ->with($this->equalTo($canReturn))
                ->willReturnSelf();
        }
        $item->expects($this->once())
            ->method('getCanReturnToStock')
            ->willReturn($canReturnToStock);

        return $dependencies;
    }

    public function testCanReturnItemToStockEmpty()
    {
        $stockConfiguration = $this->getMockBuilder(\Magento\CatalogInventory\Model\Configuration::class)
            ->disableOriginalConstructor()
            ->setMethods(['canSubtractQty', '__wakeup'])
            ->getMock();
        $stockConfiguration->expects($this->once())
            ->method('canSubtractQty')
            ->willReturn(true);

        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $block = $this->objectManagerHelper->getObject(
            \Magento\Sales\Block\Adminhtml\Items\AbstractItems::class,
            [
                'stockConfiguration' => $stockConfiguration
            ]
        );
        $result = $block->canReturnItemToStock();
        $this->assertTrue($result);
    }

    /**
     * @return array
     */
    public function canReturnItemToStockDataProvider()
    {
        return [
            [true, ['has_can_return_to_stock' => true], true],
            [false, ['has_can_return_to_stock' => true], false],
            [false, ['has_can_return_to_stock' => false, 'product_id' => 2, 'manage_stock' => false], false],
            [true, ['has_can_return_to_stock' => false, 'product_id' => 2, 'manage_stock' => true], true],
        ];
    }
}
