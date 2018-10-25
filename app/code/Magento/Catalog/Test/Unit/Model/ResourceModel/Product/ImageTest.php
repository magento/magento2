<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Image;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;

class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var Generator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorMock;

    /**
     * @var ResourceConnection | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var Image
     */
    protected $imageModel;

    /**
     * @var int
     */
    protected $imagesCount = 50;

    /**
     * @var int
     */
    protected $batchSize = 10;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->connectionMock = $this->createMock(AdapterInterface::class);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->method('getTableName')->willReturnArgument(0);

        $this->generatorMock = $this->createMock(Generator::class);

        $this->imageModel = $objectManager->getObject(
            Image::class,
            [
                'generator' => $this->generatorMock,
                'resourceConnection' => $this->resourceMock,
                'batchSize' => $this->batchSize
            ]
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getVisibleImagesSelectMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->once())
            ->method('distinct')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('from')
            ->with(
                ['images' => Gallery::GALLERY_TABLE],
                'value as filepath'
            )->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('disabled = 0')
            ->willReturnSelf();

        return $selectMock;
    }

    public function testGetCountAllProductImages(): void
    {
        $selectMock = $this->getVisibleImagesSelectMock();
        $selectMock->expects($this->exactly(2))
            ->method('reset')
            ->withConsecutive(
                ['columns'],
                ['distinct']
            )->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('columns')
            ->with(new \Zend_Db_Expr('count(distinct value)'))
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($selectMock)
            ->willReturn($this->imagesCount);

        $this->assertSame($this->imagesCount, $this->imageModel->getCountAllProductImages());
    }

    public function testGetAllProductImages(): void
    {
        $getBatchIteratorMock = function ($selectMock, $imagesCount, $batchSize): array {
            $result = [];
            $count = $imagesCount / $batchSize;
            while ($count) {
                $count--;
                $result[$count] = $selectMock;
            }

            return $result;
        };

        $getAllProductImagesSelectFetchResults = function ($batchSize): array {
            $result = [];
            $count = $batchSize;
            while ($count) {
                $count--;
                $result[$count] = $count;
            }

            return $result;
        };

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->getVisibleImagesSelectMock());

        $fetchResult = $getAllProductImagesSelectFetchResults($this->batchSize);
        $this->connectionMock->expects($this->exactly($this->imagesCount / $this->batchSize))
            ->method('fetchAll')
            ->willReturn($fetchResult);

        /** @var Select | \PHPUnit_Framework_MockObject_MockObject $selectMock */
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $batchIteratorMock = $getBatchIteratorMock($selectMock, $this->imagesCount, $this->batchSize);
        $this->generatorMock->expects($this->once())
            ->method('generate')
            ->with(
                'value_id',
                $selectMock,
                $this->batchSize,
                \Magento\Framework\DB\Query\BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
            )->willReturn($batchIteratorMock);

        $this->assertCount($this->imagesCount, $this->imageModel->getAllProductImages());
    }
}
