<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Block\Product\Review as ReviewBlock;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReviewTest extends TestCase
{
    /**
     * @var \Magento\Review\Block\Product\Review
     */
    private $block;

    /**
     * @var Collection|MockObject
     */
    private $collection;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var StoreManager|MockObject
     */
    private $storeManager;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setUp(): void
    {
        $this->initContextMock();
        $this->initRegistryMock();
        $this->initCollectionMocks();

        $helper = new ObjectManager($this);
        $this->block = $helper->getObject(
            ReviewBlock::class,
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'collectionFactory' => $this->collectionFactory,
            ]
        );
    }

    /**
     * @covers \Magento\Review\Block\Product\Review::getIdentities()
     */
    public function testGetIdentities()
    {
        static::assertEquals([Review::CACHE_TAG], $this->block->getIdentities());
    }

    /**
     * Create mocks for collection and its factory
     */
    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function initCollectionMocks()
    {
        $this->collection = $this->createPartialMock(
            Collection::class,
            ['addStoreFilter', 'addStatusFilter', 'addEntityFilter', 'getSize', '__wakeup']
        );

        $this->collection->expects(static::any())
            ->method('addStoreFilter')
            ->willReturnSelf();

        $this->collection->expects(static::any())
            ->method('addStatusFilter')
            ->with(Review::STATUS_APPROVED)
            ->willReturnSelf();

        $this->collection->expects(static::any())
            ->method('addEntityFilter')
            ->willReturnSelf();

        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->collectionFactory->method('create')->willReturn($this->collection);
    }

    /**
     * Create mock for registry object
     */
    private function initRegistryMock()
    {
        $this->initProductMock();
        $this->registry = $this->createPartialMock(Registry::class, ['registry']);

        $this->registry->expects($this->any())
            ->method('registry')
            ->with('product')
            ->willReturn($this->product);
    }

    /**
     * Create mock object for catalog product
     */
    private function initProductMock()
    {
        $this->product = $this->createPartialMock(Product::class, ['getId']);
    }

    /**
     * Create mock object for context
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function initContextMock()
    {
        $this->store = $this->createPartialMock(Store::class, ['getId', '__wakeup']);

        $this->storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $this->storeManager->method('getStore')->willReturn($this->store);

        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->context = $this->createMock(Context::class);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->context->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->context->expects($this->any())->method('getStoreManager')->willReturn($this->storeManager);
    }

    /**
     * @param bool   $isSecure
     * @param string $actionUrl
     * @param int    $productId
     */
    #[DataProvider('getProductReviewUrlDataProvider')]
    public function testGetProductReviewUrl($isSecure, $actionUrl, $productId)
    {
        $this->urlBuilder->expects($this->any())
            ->method('getUrl')
            ->with('review/product/listAjax', ['_secure' => $isSecure, 'id' => $productId])
            ->willReturn($actionUrl . '/id/' . $productId);
        $this->product->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn($isSecure);

        $this->assertEquals($actionUrl . '/id/' . $productId, $this->block->getProductReviewUrl());
    }

    /**
     * @return array
     */
    public static function getProductReviewUrlDataProvider()
    {
        return [
            [false, 'http://localhost/review/product/listAjax', 3],
            [true, 'https://localhost/review/product/listAjax' ,3],
        ];
    }
}
