<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Ui\DataProvider\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\Review\Ui\DataProvider\Product\ReviewDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReviewDataProviderTest extends TestCase
{
    /**
     * @var ReviewDataProvider
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $collectionMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock = $this->objectManager->getCollectionMock(Collection::class, []);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->model = $this->objectManager->getObject(ReviewDataProvider::class, [
            'name' => 'testName',
            'primaryFieldName' => 'testPrimaryFieldName',
            'requestFieldName' => 'testRequestFieldName',
            'meta' => [],
            'data' => [],
            'collectionFactory' => $this->collectionFactoryMock,
            'request' => $this->requestMock,
        ]);
    }

    public function testGetData()
    {
        $expected = [
            'totalRecords' => null,
            'items' => [],
        ];

        $this->collectionMock->expects($this->once())
            ->method('addEntityFilter')
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('addStoreData')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('current_product_id', 0)
            ->willReturn(1);

        $this->assertSame($expected, $this->model->getData());
    }
}
