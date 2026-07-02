<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Related;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractDataProviderTestCase extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var ProductLinkRepositoryInterface|MockObject
     */
    protected $productLinkRepositoryMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $collectionMock;

    /**
     * @return AbstractDataProvider
     */
    abstract protected function getModel();

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $objects = [
            [
                PoolInterface::class,
                $this->createMock(PoolInterface::class)
            ]
        ];
        $helper->prepareObjectManager($objects);
        $this->objectManager = new ObjectManager($this);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->productLinkRepositoryMock = $this->createMock(ProductLinkRepositoryInterface::class);
        $this->productMock = $this->createMock(ProductInterface::class);
        $this->collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);

        $this->productRepositoryMock->method('getById')->willReturn($this->productMock);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);
    }

    public function testGetCollection()
    {
        $this->collectionMock->expects($this->once())
            ->method('addAttributeToFilter');
        $this->productLinkRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn([]);
        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturn(1);

        $this->assertInstanceOf(Collection::class, $this->getModel()->getCollection());
    }
}
