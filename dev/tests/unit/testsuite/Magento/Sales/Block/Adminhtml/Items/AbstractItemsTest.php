<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Adminhtml\Items;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class AbstractItemsTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testGetItemRenderer()
    {
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            array('getChildName', 'getBlock', 'getGroupChildNames'),
            array(),
            '',
            false
        );
        $layout->expects($this->any())
            ->method('getChildName')
            ->with(null, 'some-type')
            ->will($this->returnValue('column_block-name'));
        $layout->expects($this->any())
            ->method('getGroupChildNames')
            ->with(null, 'column')
            ->will($this->returnValue(array('column_block-name')));

        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer $renderer */
        $renderer = $this->objectManagerHelper
            ->getObject('Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer');
        $renderer->setLayout($layout);

        $layout->expects($this->any())
            ->method('getBlock')
            ->with('column_block-name')
            ->will($this->returnValue($renderer));

        /** @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems $block */
        $block = $this->objectManagerHelper->getObject('Magento\Sales\Block\Adminhtml\Items\AbstractItems');
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
        $renderer = $this->getMock('StdClass');
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            array('getChildName', 'getBlock', '__wakeup'),
            array(),
            '',
            false
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
            'Magento\Sales\Block\Adminhtml\Items\AbstractItems',
            array(
                'context' => $this->objectManagerHelper->getObject(
                    'Magento\Backend\Block\Template\Context',
                    array('layout' => $layout)
                )
            )
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
        $isItem = $itemConfig['is_item'];
        $productId = isset($itemConfig['product_id']) ? $itemConfig['product_id'] : null;
        $manageStock = isset($itemConfig['manage_stock']) ? $itemConfig['manage_stock'] : null;
        $item = null;

        if ($isItem) {
            $item = $this->getMock(
                'Magento\Sales\Model\Order\Creditmemo\Item',
                ['hasCanReturnToStock', 'getOrderItem', 'setCanReturnToStock', 'getCanReturnToStock', '__wakeup'],
                [],
                '',
                false
            );
            $dependencies = $this->prepareServiceMockDependency(
                $item,
                $canReturnToStock,
                $productId,
                $manageStock,
                $itemConfig
            );
        } else {
            $dependencies = $this->prepareScopeConfigMockDependency($canReturnToStock);

        }

        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $block = $this->objectManagerHelper->getObject(
            'Magento\Sales\Block\Adminhtml\Items\AbstractItems',
            $dependencies
        );
        $this->assertSame($result, $block->canReturnItemToStock($item));
    }

    /**
     * @param bool $canReturnToStock
     * @return array
     */
    protected function prepareScopeConfigMockDependency($canReturnToStock)
    {
        $dependencies = [];
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(\Magento\CatalogInventory\Model\Stock\Item::XML_PATH_CAN_SUBTRACT),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            )
            ->will($this->returnValue($canReturnToStock));

        $dependencies['context'] = $this->objectManagerHelper->getObject(
            'Magento\Backend\Block\Template\Context',
            array('scopeConfig' => $scopeConfig)
        );
        return $dependencies;
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
        $item->expects($this->once())
            ->method('hasCanReturnToStock')
            ->will($this->returnValue($itemConfig['has_can_return_to_stock']));
        if (!$itemConfig['has_can_return_to_stock']) {
            $orderItem = $this->getMock(
                'Magento\Sales\Model\Order\Item',
                ['getProductId', '__wakeup'],
                [],
                '',
                false
            );
            $orderItem->expects($this->once())
                ->method('getProductId')
                ->will($this->returnValue($productId));
            $item->expects($this->once())
                ->method('getOrderItem')
                ->will($this->returnValue($orderItem));
            if ($productId) {
                $stockItemService = $this->getMock(
                    'Magento\CatalogInventory\Service\V1\StockItemService',
                    [],
                    [],
                    '',
                    false
                );
                $stockItemService->expects($this->once())
                    ->method('getManageStock')
                    ->with($this->equalTo($productId))
                    ->will($this->returnValue($manageStock));
                $dependencies['stockItemService'] = $stockItemService;
            }
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

    /**
     * @return array
     */
    public function canReturnItemToStockDataProvider()
    {
        return [
            [true, ['is_item' => null], true],
            [false, ['is_item' => null], false],
            [
                true,
                [
                    'is_item' => true,
                    'has_can_return_to_stock' => true
                ],
                true
            ],
            [
                false,
                [
                    'is_item' => true,
                    'has_can_return_to_stock' => true
                ],
                false
            ],
            [
                false,
                [
                    'is_item' => true,
                    'has_can_return_to_stock' => false,
                    'product_id' => 2,
                    'manage_stock' => false
                ],
                false
            ],
            [
                true,
                [
                    'is_item' => true,
                    'has_can_return_to_stock' => false,
                    'product_id' => 2,
                    'manage_stock' => true
                ],
                true
            ],
        ];
    }
}
