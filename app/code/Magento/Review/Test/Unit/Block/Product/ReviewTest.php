<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Unit\Block\Product;

use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Review\Block\Product\Review as ReviewBlock;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

/**
 * Class ReviewTest
 * @package Magento\Review\Test\Unit\Block\Product
 */
class ReviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Review\Block\Product\Review
     */
    private $block;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\UrlInterface|PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    protected function setUp()
    {
        $this->initContextMock();
        $this->initRegistryMock();
        $this->initCollectionMocks();

        $helper = new ObjectManager($this);
        $this->block = $helper->getObject(ReviewBlock::class, [
            'context' => $this->context,
            'registry' => $this->registry,
            'collectionFactory' => $this->collectionFactory,
        ]);
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
        $this->urlBuilder = $this->getMockBuilder('Magento\Framework\UrlInterface')->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();
        $this->context = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
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

    public function getProductReviewUrlDataProvider()
    {
        return [
            [false, 'http://localhost/review/product/listAjax', 3],
            [true, 'https://localhost/review/product/listAjax' ,3],
        ];
    }
}
