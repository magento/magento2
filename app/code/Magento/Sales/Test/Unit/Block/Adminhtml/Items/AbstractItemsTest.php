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

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
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
            ->will($this->returnValue($this->stockItemMock));
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
            ->will($this->returnValue('column_block-name'));
        $layout->expects($this->any())
            ->method('getGroupChildNames')
            ->with(null, 'column')
            ->will($this->returnValue(['column_block-name']));

        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer $renderer */
        $renderer = $this->objectManagerHelper
            ->getObject(\Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer::class);
        $renderer->setLayout($layout);

        $layout->expects($this->any())
            ->method('getBlock')
            ->with('column_block-name')
            ->will($this->returnValue($renderer));

        /** @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems $block */
        $block = $this->objectManagerHelper->getObject(\Magento\Sales\Block\Adminhtml\Items\AbstractItems::class);
        $block->setLayout($layout);

        $this->assertSame($renderer, $block->getItemRenderer('some-type'));
        $this->assertSame($renderer, $renderer->getColumnRenderer('block-name'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Renderer for type "some-type" does not exist.
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $renderer = $this->createMock(\stdClass::class);
        $layout = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock', '__wakeup']
        );
        $layout->expects($this->at(0))
            ->method('getChildName')
            ->with(null, 'some-type')
            ->will($this->returnValue('some-block-name'));
        $layout->expects($this->at(1))
            ->method('getBlock')
            ->with('some-block-name')
            ->will($this->returnValue($renderer));

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
     * @param \PHPUnit_Framework_MockObject_MockObject $item
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
            ->will($this->returnValue($manageStock));
        $dependencies['stockRegistry'] = $this->stockRegistry;

        $item->expects($this->once())
            ->method('hasCanReturnToStock')
            ->will($this->returnValue($itemConfig['has_can_return_to_stock']));
        if (!$itemConfig['has_can_return_to_stock']) {
            $orderItem = $this->createPartialMock(
                \Magento\Sales\Model\Order\Item::class,
                ['getProductId', '__wakeup', 'getStore']
            );

            $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
            $store->expects($this->once())
                ->method('getWebsiteId')
                ->will($this->returnValue(10));
            $orderItem->expects($this->once())
                ->method('getStore')
                ->will($this->returnValue($store));

            $orderItem->expects($this->once())
                ->method('getProductId')
                ->will($this->returnValue($productId));
            $item->expects($this->any())
                ->method('getOrderItem')
                ->will($this->returnValue($orderItem));
            if ($productId && $manageStock) {
                $canReturn = true;
            } else {
                $canReturn = false;
            }
            $item->expects($this->once())
                ->method('setCanReturnToStock')
                ->with($this->equalTo($canReturn))
                ->will($this->returnSelf());
        }
        $item->expects($this->once())
            ->method('getCanReturnToStock')
            ->will($this->returnValue($canReturnToStock));

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
            ->will($this->returnValue(true));

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
