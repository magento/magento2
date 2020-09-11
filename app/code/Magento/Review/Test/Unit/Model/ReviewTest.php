<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\Summary;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReviewTest extends TestCase
{
    /** @var Review */
    protected $review;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var Registry|MockObject */
    protected $registryMock;

    /** @var MockObject */
    protected $productFactoryMock;

    /** @var MockObject */
    protected $statusFactoryMock;

    /** @var MockObject */
    protected $reviewSummaryMock;

    /** @var MockObject */
    protected $summaryModMock;

    /** @var Summary|MockObject */
    protected $summaryMock;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManagerMock;

    /** @var UrlInterface|MockObject */
    protected $urlInterfaceMock;

    /** @var \Magento\Review\Model\ResourceModel\Review|MockObject */
    protected $resource;

    /** @var int */
    protected $reviewId = 8;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->productFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->statusFactoryMock = $this->createPartialMock(
            \Magento\Review\Model\ResourceModel\Review\Status\CollectionFactory::class,
            ['create']
        );
        $this->reviewSummaryMock = $this->createMock(
            \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory::class
        );
        $this->summaryModMock = $this->createPartialMock(
            SummaryFactory::class,
            ['create']
        );
        $this->summaryMock = $this->createMock(Summary::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->urlInterfaceMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->resource = $this->createMock(\Magento\Review\Model\ResourceModel\Review::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->review = $this->objectManagerHelper->getObject(
            Review::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'productFactory' => $this->productFactoryMock,
                'statusFactory' => $this->statusFactoryMock,
                'summaryFactory' => $this->reviewSummaryMock,
                'summaryModFactory' => $this->summaryModMock,
                'reviewSummary' => $this->summaryMock,
                'storeManager' => $this->storeManagerMock,
                'urlModel' => $this->urlInterfaceMock,
                'resource' => $this->resource,
                'data' => ['review_id' => $this->reviewId, 'status_id' => 1, 'stores' => [2, 3, 4]]
            ]
        );
    }

    public function testGetProductCollection()
    {
        $collection = $this->createMock(Collection::class);
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $this->assertSame($collection, $this->review->getProductCollection());
    }

    public function testGetStatusCollection()
    {
        $collection = $this->createMock(\Magento\Review\Model\ResourceModel\Review\Status\Collection::class);
        $this->statusFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $this->assertSame($collection, $this->review->getStatusCollection());
    }

    public function testGetTotalReviews()
    {
        $primaryKey = 'review_id';
        $approvedOnly = false;
        $storeId = 0;
        $result = 5;
        $this->resource->expects($this->once())->method('getTotalReviews')
            ->with($primaryKey, $approvedOnly, $storeId)
            ->willReturn($result);
        $this->assertSame($result, $this->review->getTotalReviews($primaryKey, $approvedOnly, $storeId));
    }

    public function testAggregate()
    {
        $this->resource->expects($this->once())->method('aggregate')
            ->with($this->review)
            ->willReturn($this->review);
        $this->assertSame($this->review, $this->review->aggregate());
    }

    /**
     * @deprecated
     */
    public function testGetEntitySummary()
    {
        $productId = 6;
        $storeId = 4;
        $testSummaryData = ['test' => 'value'];
        $summary = new DataObject();
        $summary->setData($testSummaryData);

        $product = $this->getMockBuilder(Product::class)
            ->addMethods(['setRatingSummary'])
            ->onlyMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $product->expects($this->once())->method('setRatingSummary')->with($summary)->willReturnSelf();

        $summaryData = $this->getMockBuilder(Summary::class)
            ->addMethods(['setStoreId'])
            ->onlyMethods(['load', 'getData', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $summaryData->expects($this->once())->method('setStoreId')
            ->with($storeId)->willReturnSelf();
        $summaryData->expects($this->once())->method('load')
            ->with($productId)->willReturnSelf();
        $summaryData->expects($this->once())->method('getData')->willReturn($testSummaryData);
        $this->summaryModMock->expects($this->once())->method('create')->willReturn($summaryData);
        $this->assertNull($this->review->getEntitySummary($product, $storeId));
    }

    public function testGetPendingStatus()
    {
        $this->assertSame(Review::STATUS_PENDING, $this->review->getPendingStatus());
    }

    public function testGetReviewUrl()
    {
        $result = 'http://some.url';
        $this->urlInterfaceMock->expects($this->once())->method('getUrl')
            ->with('review/product/view', ['id' => $this->reviewId])
            ->willReturn($result);
        $this->assertSame($result, $this->review->getReviewUrl());
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param string $result
     * @dataProvider getProductUrlDataProvider
     */
    public function testGetProductUrl($productId, $storeId, $result)
    {
        if ($storeId) {
            $this->urlInterfaceMock->expects($this->once())->method('setScope')
                ->with($storeId)->willReturnSelf();
        }

        $this->urlInterfaceMock->expects($this->once())->method('getUrl')
            ->with('catalog/product/view', ['id' => $productId])
            ->willReturn($result);
        $this->assertSame($result, $this->review->getProductUrl($productId, $storeId));
    }

    /**
     * @return array
     */
    public function getProductUrlDataProvider()
    {
        return [
            'store id specified' => [3, 5, 'http://some.url'],
            'store id is not specified' => [3, null, 'http://some.url/2/'],
        ];
    }

    public function testIsApproved()
    {
        $this->assertTrue($this->review->isApproved());
    }

    /**
     * @param int|null $storeId
     * @param bool $result
     * @dataProvider isAvailableOnStoreDataProvider
     */
    public function testIsAvailableOnStore($storeId, $result)
    {
        $store = $this->createMock(Store::class);
        if ($storeId) {
            $store->expects($this->once())->method('getId')->willReturn($storeId);
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->with($store)
                ->willReturn($store);
        }
        $this->assertSame($result, $this->review->isAvailableOnStore($store));
    }

    /**
     * @return array
     */
    public function isAvailableOnStoreDataProvider()
    {
        return [
            'store id is set and not in list' => [1, false],
            'store id is set' => [3, true],
            'store id is not set' => [null, false],
        ];
    }

    public function testGetEntityIdByCode()
    {
        $entityCode = 'test';
        $result = 22;
        $this->resource->expects($this->once())->method('getEntityIdByCode')
            ->with($entityCode)
            ->willReturn($result);
        $this->assertSame($result, $this->review->getEntityIdByCode($entityCode));
    }

    public function testGetIdentities()
    {
        $this->review->setStatusId(Review::STATUS_PENDING);
        $this->assertEmpty($this->review->getIdentities());

        $productId = 1;
        $this->review->setEntityPkValue($productId);
        $this->review->setStatusId(Review::STATUS_PENDING);
        $this->assertEquals([Product::CACHE_TAG . '_' . $productId], $this->review->getIdentities());

        $this->review->setEntityPkValue($productId);
        $this->review->setStatusId(Review::STATUS_APPROVED);
        $this->assertEquals([Product::CACHE_TAG . '_' . $productId], $this->review->getIdentities());

        $this->review->setEntityPkValue($productId);
        $this->review->setStatusId(Review::STATUS_NOT_APPROVED);
        $this->assertEquals([Product::CACHE_TAG . '_' . $productId], $this->review->getIdentities());
    }
}
