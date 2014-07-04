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
namespace Magento\Sales\Block\Reorder;

/**
 * Class SidebarTest
 *
 * @package Magento\Sales\Block\Reorder
 */
class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Reorder\Sidebar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContext;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollection;

    /** @var \Magento\CatalogInventory\Service\V1\StockItemService|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemService;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->httpContext = $this->getMock('Magento\Framework\App\Http\Context', ['getValue'], [], '', false);
        $this->orderCollectionFactory = $this->getMock(
            'Magento\Sales\Model\Resource\Order\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->customerSession = $this->getMock(
            'Magento\Customer\Model\Session',
            ['getCustomerId'],
            [],
            '',
            false
        );
        $this->orderConfig = $this->getMock(
            'Magento\Sales\Model\Order\Config',
            ['getVisibleOnFrontStatuses'],
            [],
            '',
            false
        );
        $this->orderCollection = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Collection',
            [
                'addAttributeToFilter',
                'addAttributeToSort',
                'setPage',
                'setOrders'
            ],
            [],
            '',
            false
        );
        $this->stockItemService = $this->getMock(
            'Magento\CatalogInventory\Service\V1\StockItemService',
            [],
            [],
            '',
            false
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    protected function createBlockObject()
    {
        $this->block = $this->objectManagerHelper->getObject(
            'Magento\Sales\Block\Reorder\Sidebar',
            [
                'context' => $this->context,
                'orderCollectionFactory' => $this->orderCollectionFactory,
                'orderConfig' => $this->orderConfig,
                'customerSession' => $this->customerSession,
                'httpContext' => $this->httpContext,
                'stockItemService' => $this->stockItemService,
            ]
        );
    }

    public function testGetIdentities()
    {
        $websiteId = 1;
        $storeId = null;
        $productTags = ['catalog_product_1'];
        $limit = 5;

        $storeManager = $this->getMock('Magento\Store\Model\StoreManager', ['getStore'], [], '', false);
        $this->context->expects($this->once())
            ->method('getStoreManager')
            ->will($this->returnValue($storeManager));

        $store = $this->getMock('Magento\Store\Model', ['getWebsiteId'], [], '', false);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));
        $storeManager->expects($this->once())
            ->method('getStore')
            ->with($this->equalTo($storeId))
            ->will($this->returnValue($store));

        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeUp', 'getIdentities', 'getWebsiteIds'],
            [],
            '',
            false
        );
        $product->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($productTags));
        $product->expects($this->atLeastOnce())
            ->method('getWebsiteIds')
            ->will($this->returnValue([$websiteId]));

        $item = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Item',
            ['__wakeup', 'getProduct'],
            [],
            '',
            false
        );
        $item->expects($this->atLeastOnce())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['__wakeup', 'getParentItemsRandomCollection'],
            [],
            '',
            false
        );
        $order->expects($this->atLeastOnce())
            ->method('getParentItemsRandomCollection')
            ->with($this->equalTo($limit))
            ->will($this->returnValue([$item]));

        $this->createBlockObject();
        $this->assertSame($this->block, $this->block->setOrders([$order]));
        $this->assertEquals($productTags, $this->block->getIdentities());
    }

    public function testInitOrders()
    {
        $customerId = 25;
        $attribute = ['customer_id', 'status'];

        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo(\Magento\Customer\Helper\Data::CONTEXT_AUTH))
            ->will($this->returnValue(true));

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));

        $statuses = ['pending', 'processing', 'complete'];
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->will($this->returnValue($statuses));

        $this->orderCollection->expects($this->at(0))
            ->method('addAttributeToFilter')
            ->with(
                $attribute[0],
                $this->equalTo($customerId)
            )
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->at(1))
            ->method('addAttributeToFilter')
            ->with($attribute[1], $this->equalTo(['in' => $statuses]))
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->at(2))
            ->method('addAttributeToSort')
            ->with('created_at', 'desc')
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->at(3))
            ->method('setPage')
            ->with($this->equalTo(1), $this->equalTo(1))
            ->will($this->returnSelf());

        $this->orderCollectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->orderCollection));
        $this->createBlockObject();
        $this->assertEquals($this->orderCollection, $this->block->getOrders());
    }

    /**
     * @param int|bool $productId
     * @param bool $result
     * @dataProvider isItemAvailableForReorderDataProvider
     */
    public function testIsItemAvailableForReorder($productId, $result)
    {
        if ($productId) {
            $product = $this->getMock('Magento\Catalog\Model\Product', ['getId', '__wakeup'], [], '', false);
            $product->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($productId));
            $this->stockItemService->expects($this->once())
                ->method('getIsInStock')
                ->with($this->equalTo($productId))
                ->will($this->returnValue($result));
        } else {
            $product = false;
        }
        $orderItem = $this->getMock('Magento\Sales\Model\Order\Item', [], [], '', false);
        $orderItem->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($product));
        $this->createBlockObject();
        $this->assertSame($result, $this->block->isItemAvailableForReorder($orderItem));
    }

    /**
     * @return array
     */
    public function isItemAvailableForReorderDataProvider()
    {
        return [
            [false, false],
            [4, true],
        ];
    }
}
