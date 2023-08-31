<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Items;

use Magento\Backend\Block\Template\Context;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout;
use Magento\Sales\Block\Adminhtml\Items\AbstractItems;
use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * TODO refactor me PLEASE
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractItemsTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var MockObject
     */
    protected $stockItemMock;

    /**
     * @var MockObject
     */
    protected $stockRegistry;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStockItem'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            Item::class,
            ['getManageStock']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
    }

    /**
     * @return void
     */
    public function testGetItemRenderer(): void
    {
        $layout = $this->createPartialMock(
            Layout::class,
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

        /** @var DefaultRenderer $renderer */
        $renderer = $this->objectManagerHelper
            ->getObject(DefaultRenderer::class);
        $renderer->setLayout($layout);

        $layout->expects($this->any())
            ->method('getBlock')
            ->with('column_block-name')
            ->willReturn($renderer);

        /** @var AbstractItems $block */
        $block = $this->objectManagerHelper->getObject(AbstractItems::class);
        $block->setLayout($layout);

        $this->assertSame($renderer, $block->getItemRenderer('some-type'));
        $this->assertSame($renderer, $renderer->getColumnRenderer('block-name'));
    }

    /**
     * @return void
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer(): void
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Renderer for type "some-type" does not exist.');
        $renderer = $this->createMock(\stdClass::class);
        $layout = $this->createPartialMock(
            Layout::class,
            ['getChildName', 'getBlock']
        );
        $layout->method('getChildName')
            ->with(null, 'some-type')
            ->willReturn('some-block-name');
        $layout->method('getBlock')
            ->with('some-block-name')
            ->willReturn($renderer);

        /** @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems $block */
        $block = $this->objectManagerHelper->getObject(
            AbstractItems::class,
            [
                'context' => $this->objectManagerHelper->getObject(
                    Context::class,
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
     *
     * @return void
     * @dataProvider canReturnItemToStockDataProvider
     */
    public function testCanReturnItemToStock(bool $canReturnToStock, array $itemConfig, bool $result): void
    {
        $productId = $itemConfig['product_id'] ?? null;
        $manageStock = $itemConfig['manage_stock'] ?? false;
        $item = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)->addMethods(
            ['hasCanReturnToStock', 'setCanReturnToStock', 'getCanReturnToStock']
        )
            ->onlyMethods(['getOrderItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $dependencies = $this->prepareServiceMockDependency(
            $item,
            $canReturnToStock,
            $productId,
            $manageStock,
            $itemConfig
        );

        /** @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems $block */
        $block = $this->objectManagerHelper->getObject(
            AbstractItems::class,
            $dependencies
        );
        $this->assertSame($result, $block->canReturnItemToStock($item));
    }

    /**
     * @param MockObject $item
     * @param bool $canReturnToStock
     * @param int|null $productId
     * @param bool $manageStock
     * @param array $itemConfig
     * @return array
     */
    protected function prepareServiceMockDependency(
        MockObject $item,
        bool $canReturnToStock,
        ?int $productId,
        bool $manageStock,
        array $itemConfig
    ): array {
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
                ['getProductId', 'getStore']
            );

            $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
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
                ->with($canReturn)->willReturnSelf();
        }
        $item->expects($this->once())
            ->method('getCanReturnToStock')
            ->willReturn($canReturnToStock);

        return $dependencies;
    }

    /**
     * @return void
     */
    public function testCanReturnItemToStockEmpty(): void
    {
        $stockConfiguration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['canSubtractQty'])
            ->getMock();
        $stockConfiguration->expects($this->once())
            ->method('canSubtractQty')
            ->willReturn(true);

        /** @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems $block */
        $block = $this->objectManagerHelper->getObject(
            AbstractItems::class,
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
    public function canReturnItemToStockDataProvider(): array
    {
        return [
            [true, ['has_can_return_to_stock' => true], true],
            [false, ['has_can_return_to_stock' => true], false],
            [false, ['has_can_return_to_stock' => false, 'product_id' => 2, 'manage_stock' => false], false],
            [true, ['has_can_return_to_stock' => false, 'product_id' => 2, 'manage_stock' => true], true]
        ];
    }
}
