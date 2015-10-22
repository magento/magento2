<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    private $collectionMock;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @va rMagento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    protected function setUp()
    {
        $this->initContextMock();
        $this->initRegistryMock();
        $this->initCollectionMocks();

        $helper = new ObjectManager($this);
        $this->block = $helper->getObject(ReviewBlock::class, [
            'context' => $this->contextMock,
            'registry' => $this->registryMock,
            'collectionFactory' => $this->collectionFactoryMock,
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
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addStoreFilter', 'addStatusFilter', 'addEntityFilter', 'getSize', '__wakeup'])
            ->getMock();

        $this->collectionMock->expects(static::any())
            ->method('addStoreFilter')
            ->willReturnSelf();

        $this->collectionMock->expects(static::any())
            ->method('addStatusFilter')
            ->with(Review::STATUS_APPROVED)
            ->willReturnSelf();

        $this->collectionMock->expects(static::any())
            ->method('addEntityFilter')
            ->willReturnSelf();

        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', '__wakeup'])
            ->getMock();

        $this->collectionFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->collectionMock);
    }

    /**
     * Create mock for registry object
     */
    private function initRegistryMock()
    {
        $this->initProductMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();

        $this->registryMock->expects(static::once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);
    }

    /**
     * Create mock object for catalog product
     */
    private function initProductMock()
    {
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
    }

    /**
     * Create mock object for context
     */
    private function initContextMock()
    {
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();

        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();

        $storeManager->expects(static::once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreManager'])
            ->getMock();

        $this->contextMock->expects(static::once())
            ->method('getStoreManager')
            ->willReturn($storeManager);
    }
}
