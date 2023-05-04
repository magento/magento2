<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleAnalytics\Test\Unit\Block;

use Magento\Cookie\Helper\Cookie;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\GoogleAnalytics\Block\Ga;
use Magento\GoogleAnalytics\Helper\Data;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GaTest extends TestCase
{

    /**
     * @var Ga|MockObject
     */
    protected $gaBlock;

    /**
     * @var MockObject
     */
    private $cookieHelperMock;

    /**
     * @var MockObject
     */
    private $salesOrderCollectionMock;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    /**
     * @var MockObject
     */
    private $storeMock;

    /**
     * @var MockObject
     */
    private $googleAnalyticsDataMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($objectManager->getObject(Escaper::class));

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->once())->method('getStoreManager')->willReturn($this->storeManagerMock);

        $this->salesOrderCollectionMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->googleAnalyticsDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieHelperMock = $this->getMockBuilder(Cookie::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->gaBlock = $objectManager->getObject(
            Ga::class,
            [
                'context' => $contextMock,
                'salesOrderCollection' => $this->salesOrderCollectionMock,
                'googleAnalyticsData' => $this->googleAnalyticsDataMock,
                'cookieHelper' => $this->cookieHelperMock
            ]
        );
    }

    public function testOrderTrackingCode()
    {
        $this->salesOrderCollectionMock->expects($this->once())
            ->method('create')
            ->willReturn($this->createCollectionMock());
        $this->storeMock->expects($this->once())->method('getFrontendName')->willReturn('test');
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);

        $expectedCode = "ga('require', 'ec', 'ec.js');
            ga('set', 'currencyCode', 'USD');
            ga('ec:addProduct', {
                                    'id': 'sku0',
                                    'name': 'testName0',
                                    'price': 0.00,
                                    'quantity': 1
                                });
            ga('ec:addProduct', {
                                    'id': 'sku1',
                                    'name': 'testName1',
                                    'price': 1.00,
                                    'quantity': 1.11
                                });
            ga('ec:setAction', 'purchase', {
                                'id': '100',
                                'affiliation': 'test',
                                'revenue': 10.00,
                                'tax': 2.00,
                                'shipping': 2.00
                            });
            ga('send', 'pageview');";

        $this->gaBlock->setOrderIds([1, 2]);
        $this->assertEquals(
            $this->packString($expectedCode),
            $this->packString($this->gaBlock->getOrdersTrackingCode())
        );
    }

    public function testIsCookieRestrictionModeEnabled()
    {
        $this->cookieHelperMock->expects($this->once())->method('isCookieRestrictionModeEnabled')->willReturn(false);
        $this->assertFalse($this->gaBlock->isCookieRestrictionModeEnabled());
    }

    public function testGetCurrentWebsiteId()
    {
        $websiteId = 100;
        $websiteMock = $this->getMockBuilder(WebsiteInterface::class)
            ->getMock();
        $websiteMock->expects($this->once())->method('getId')->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())->method('getWebsite')->willReturn($websiteMock);
        $this->assertEquals($websiteId, $this->gaBlock->getCurrentWebsiteId());
    }

    public function testOrderTrackingData()
    {
        $this->salesOrderCollectionMock->expects($this->once())
            ->method('create')
            ->willReturn($this->createCollectionMock());
        $this->storeMock->expects($this->once())->method('getFrontendName')->willReturn('test');
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);

        $expectedResult = [
            'orders' => [
                [
                    'id' => 100,
                    'affiliation' => 'test',
                    'revenue' => 10.00,
                    'tax' => 2.00,
                    'shipping' => 2.0
                ]
            ],
            'products' => [
                [
                    'id' => 'sku0',
                    'name' => 'testName0',
                    'price' => 0.00,
                    'quantity' => 1
                ],
                [
                    'id' => 'sku1',
                    'name' => 'testName1',
                    'price' => 1.00,
                    'quantity' => 1.11
                ]
            ],
            'currency' => 'USD'
        ];

        $this->gaBlock->setOrderIds([1, 2]);
        $this->assertEquals($expectedResult, $this->gaBlock->getOrdersTrackingData());
    }

    public function testGetPageTrackingData()
    {
        $pageName = '/page/name';
        $accountId = 100;
        $expectedResult = [
            'optPageUrl' => ", '" . $pageName . "'",
            'isAnonymizedIpActive' => true,
            'accountId' => $accountId
        ];
        $this->gaBlock->setData('page_name', $pageName);
        $this->googleAnalyticsDataMock->expects($this->once())->method('isAnonymizedIpActive')->willReturn(true);

        $this->assertEquals($expectedResult, $this->gaBlock->getPageTrackingData($accountId));
    }

    /**
     * Create Order mock with $orderItemCount items
     *
     * @param int $orderItemCount
     * @return Order|MockObject
     */
    protected function createOrderMock($orderItemCount = 2)
    {
        $orderItems = [];
        for ($i = 0; $i < $orderItemCount; $i++) {
            $orderItemMock = $this->getMockBuilder(OrderItemInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
            $orderItemMock->expects($this->once())->method('getSku')->willReturn('sku' . $i);
            $orderItemMock->expects($this->once())->method('getName')->willReturn('testName' . $i);
            $orderItemMock->expects($this->once())->method('getPrice')->willReturn((float)($i . '.0000'));
            $orderItemMock->expects($this->once())->method('getQtyOrdered')->willReturn($i == 1 ? 1.11 : $i + 1);
            $orderItems[] = $orderItemMock;
        }

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())->method('getIncrementId')->willReturn(100);
        $orderMock->expects($this->once())->method('getAllVisibleItems')->willReturn($orderItems);
        $orderMock->expects($this->once())->method('getGrandTotal')->willReturn(10.00);
        $orderMock->expects($this->once())->method('getTaxAmount')->willReturn(2.00);
        $orderMock->expects($this->once())->method('getShippingAmount')->willReturn(round((float)$orderItemCount, 2));
        $orderMock->expects($this->once())->method('getOrderCurrencyCode')->willReturn('USD');
        return $orderMock;
    }

    /**
     * @return Collection|MockObject
     */
    protected function createCollectionMock()
    {
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->createOrderMock(2)]));
        return $collectionMock;
    }

    /**
     * Removes from $string whitespace characters
     *
     * @param string $string
     * @return string
     */
    protected function packString($string)
    {
        return preg_replace('/\s/', '', $string);
    }
}
