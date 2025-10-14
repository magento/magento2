<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Test\Unit\Block;

use Magento\Cookie\Helper\Cookie;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\GoogleGtag\Block\Ga;
use Magento\GoogleGtag\Model\Config\GtagConfig as GtagConfiguration;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
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
    private $storeManagerMock;

    /**
     * @var MockObject
     */
    private $storeMock;

    /**
     * @var GtagConfiguration|mixed|MockObject
     */
    private $googleGtagConfig;

    /**
     * @var SearchCriteriaBuilder|mixed|MockObject
     */
    private $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface|mixed|MockObject
     */
    private $orderRepository;
    /**
     * @var SerializerInterface|mixed|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->serializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->once())->method('getStoreManager')->willReturn($this->storeManagerMock);

        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->onlyMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->onlyMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->googleGtagConfig = $this->getMockBuilder(GtagConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieHelperMock = $this->getMockBuilder(Cookie::class)
            ->disableOriginalConstructor()
            ->getMock();

        $escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $escaper->expects($this->any())
            ->method('escapeHtmlAttr')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $escaper->expects($this->any())
            ->method('escapeHtml')
            ->willReturnOnConsecutiveCalls('sku0', 'testName0', 'test');

        $this->gaBlock = $objectManager->getObject(
            Ga::class,
            [
                'context' => $contextMock,
                'googleGtagConfig' => $this->googleGtagConfig,
                'cookieHelper' => $this->cookieHelperMock,
                'serializer' => $this->serializerMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'orderRepository' => $this->orderRepository,
                '_escaper' => $escaper
            ]
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

    /**
     * Test for getOrdersTrackingData()
     * @return void
     */
    public function testOrderTrackingData()
    {
        $searchCriteria = $this
            ->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderSearchResult = $this->getMockBuilder(OrderSearchResultInterface::class)
            ->onlyMethods(['getTotalCount', 'getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderRepository->method('getList')->willReturn($orderSearchResult);
        $orderSearchResult->method('getTotalCount')->willReturn(1);
        $orderSearchResult->method('getItems')->willReturn([ 1 => $this->createOrderMock(1)]);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $this->storeMock->expects($this->once())->method('getFrontendName')->willReturn('test');
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $expectedResult = [
            'orders' => [
                [
                    'transaction_id' => 100,
                    'value' => 10.00,
                    'tax' => 2.00,
                    'shipping' => 1.00,
                    'currency' => 'USD'
                ]
            ],
            'products' => [
                [
                    'item_id' => 'sku0',
                    'item_name' => 'testName0',
                    'affiliation' => 'test',
                    'price' => 0.00,
                    'quantity' => 1
                ]
            ],
        ];
        $this->gaBlock->setOrderIds([1, 2]);
        $tempResults = $this->gaBlock->getOrdersTrackingData();
        $this->assertEquals($expectedResult, $tempResults);
    }

    public function testGetPageTrackingData()
    {
        $pageName = '/page/name';
        $accountId = "100";
        $expectedResult = [
            'optPageUrl' => ", '" . $pageName . "'",
            'measurementId' => $accountId
        ];
        $this->gaBlock->setData('page_name', $pageName);
        $this->assertEquals($expectedResult, $this->gaBlock->getPageTrackingData($accountId));
    }

    /**
     * Create Order mock with $orderItemCount items
     * @param int $orderItemCount
     * @return Order|MockObject
     */
    protected function createOrderMock($orderItemCount = 1)
    {
        $orderItems = [];
        for ($i = 0; $i < $orderItemCount; $i++) {
            $orderItemMock = $this->getMockBuilder(OrderItemInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
            $orderItemMock->expects($this->once())->method('getSku')->willReturn('sku' . $i);
            $orderItemMock->expects($this->once())->method('getName')->willReturn('testName' . $i);
            $orderItemMock->expects($this->once())->method('getPrice')->willReturn($i . '.00');
            $orderItemMock->expects($this->once())->method('getQtyOrdered')->willReturn($i + 1);
            $orderItems[] = $orderItemMock;
        }

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())->method('getIncrementId')->willReturn(100);
        $orderMock->expects($this->once())->method('getAllVisibleItems')->willReturn($orderItems);
        $orderMock->expects($this->once())->method('getGrandTotal')->willReturn(10);
        $orderMock->expects($this->once())->method('getTaxAmount')->willReturn(2);
        $orderMock->expects($this->once())->method('getShippingAmount')->willReturn($orderItemCount);
        $orderMock->expects($this->once())->method('getOrderCurrencyCode')->willReturn('USD');
        return $orderMock;
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
