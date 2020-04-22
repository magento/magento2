<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

    /** @var Context|MockObject */
    protected $context;

    /** @var UrlInterface|MockObject */
    protected $urlBuilder;

    /** @var RequestInterface|MockObject */
    protected $requestMock;

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
    private function initCollectionMocks()
    {
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addStoreFilter', 'addStatusFilter', 'addEntityFilter', 'getSize', '__wakeup'])
            ->getMock();

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

        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', '__wakeup'])
            ->getMock();

        $this->collectionFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->collection);
    }

    /**
     * Create mock for registry object
     */
    private function initRegistryMock()
    {
        $this->initProductMock();
        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();

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
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
    }

    /**
     * Create mock object for context
     */
    private function initContextMock()
    {
        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();

        $this->storeManager->expects(static::any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->context->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->context->expects($this->any())->method('getStoreManager')->willReturn($this->storeManager);
    }

    /**
     * @param bool $isSecure
     * @param string $actionUrl
     * @param int $productId
     * @dataProvider getProductReviewUrlDataProvider
     */
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
    public function getProductReviewUrlDataProvider()
    {
        return [
            [false, 'http://localhost/review/product/listAjax', 3],
            [true, 'https://localhost/review/product/listAjax' ,3],
        ];
    }
}
