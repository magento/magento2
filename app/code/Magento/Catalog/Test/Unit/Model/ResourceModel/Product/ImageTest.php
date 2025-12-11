<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Catalog\Model\ResourceModel\Product\Image;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\BatchIteratorInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    /**
     * @var AdapterInterface | MockObject
     */
    private $connectionMock;

    /**
     * @var Generator | MockObject
     */
    private $generatorMock;

    /**
     * @var ResourceConnection | MockObject
     */
    private $resourceMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var Image
     */
    private $imageModel;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->method('getTableName')
            ->willReturnArgument(0);
        $this->generatorMock = $this->createMock(Generator::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $metadata = $this->createMock(EntityMetadataInterface::class);
        $this->metadataPoolMock->method('getMetadata')
            ->willReturn($metadata);

        $this->imageModel = new Image(
            $this->generatorMock,
            $this->resourceMock,
            $this->metadataPoolMock,
        );
    }

    /**
     * @return MockObject
     */
    private function getVisibleImagesSelectMock(): MockObject
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())
            ->method('distinct')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('from')
            ->with(['images' => Gallery::GALLERY_TABLE], 'value as filepath')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('disabled = 0')
            ->willReturnSelf();

        return $selectMock;
    }

    /**
     * @return MockObject
     */
    private function getUsedImagesSelectMock(): MockObject
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())
            ->method('distinct')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('from')
            ->with(['images' => Gallery::GALLERY_TABLE], 'value as filepath')
            ->willReturnSelf();
        $selectMock->expects($this->atLeastOnce())
            ->method('joinInner')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('images.disabled = 0 AND image_value.disabled = 0')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('group')
            ->willReturnSelf();

        return $selectMock;
    }

    /**
     * @param int $imagesCount
     * @dataProvider dataProvider
     */
    public function testGetCountAllProductImages(int $imagesCount): void
    {
        $selectMock = $this->getVisibleImagesSelectMock();
        $selectMock->expects($this->exactly(2))
            ->method('reset')
            ->willReturnCallback(
                function ($arg) use ($selectMock) {
                    if ($arg == 'columns') {
                        return $selectMock;
                    } elseif ($arg == 'distinct') {
                        return $selectMock;
                    }
                }
            );
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
            ->willReturn($imagesCount);

        $this->assertSame(
            $imagesCount,
            $this->imageModel->getCountAllProductImages()
        );
    }

    /**
     * @param int $imagesCount
     * @dataProvider dataProvider
     */
    public function testGetCountUsedProductImages(int $imagesCount): void
    {
        $selectMock = $this->getUsedImagesSelectMock();
        $selectMock->expects($this->exactly(2))
            ->method('reset')
            ->willReturnCallback(
                function ($arg) use ($selectMock) {
                    if ($arg == 'columns') {
                        return $selectMock;
                    } elseif ($arg == 'distinct') {
                        return $selectMock;
                    }
                }
            );
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
            ->willReturn($imagesCount);

        $this->assertSame(
            $imagesCount,
            $this->imageModel->getCountUsedProductImages()
        );
    }

    /**
     * @param int $imagesCount
     * @param int $batchSize
     * @dataProvider dataProvider
     */
    public function testGetAllProductImages(int $imagesCount, int $batchSize): void
    {
        $selectMock = $this->getVisibleImagesSelectMock();
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);

        $batchCount = (int)ceil($imagesCount / $batchSize);
        $fetchResultsCallback = $this->getFetchResultCallbackForBatches($imagesCount, $batchSize);
        $this->connectionMock->expects($this->exactly($batchCount))
            ->method('fetchAll')
            ->willReturnCallback($fetchResultsCallback);
        $this->generatorMock->expects($this->once())
            ->method('generate')
            ->with(
                'value_id',
                $selectMock,
                $batchSize,
                BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
            )->willReturnCallback(
                $this->getBatchIteratorCallback($selectMock, $batchCount)
            );

        $imageModel = new Image(
            $this->generatorMock,
            $this->resourceMock,
            $this->metadataPoolMock,
            $batchSize,
        );
        $resultImagesCount = iterator_to_array($imageModel->getAllProductImages(), false);
        $this->assertCount($imagesCount, $resultImagesCount);
    }

    /**
     * @param int $imagesCount
     * @param int $batchSize
     * @dataProvider dataProvider
     */
    public function testGetUsedProductImages(int $imagesCount, int $batchSize): void
    {
        $selectMock = $this->getUsedImagesSelectMock();
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);

        $batchCount = (int)ceil($imagesCount / $batchSize);
        $fetchResultsCallback = $this->getFetchResultCallbackForBatches($imagesCount, $batchSize);
        $this->connectionMock->expects($this->exactly($batchCount))
            ->method('fetchAll')
            ->willReturnCallback($fetchResultsCallback);
        $this->generatorMock->expects($this->once())
            ->method('generate')
            ->with(
                'value_id',
                $selectMock,
                $batchSize,
                BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
            )->willReturnCallback(
                $this->getBatchIteratorCallback($selectMock, $batchCount)
            );

        $imageModel = new Image(
            $this->generatorMock,
            $this->resourceMock,
            $this->metadataPoolMock,
            $batchSize,
        );
        $resultImagesCount = iterator_to_array($imageModel->getUsedProductImages(), false);
        $this->assertCount($imagesCount, $resultImagesCount);
    }

    /**
     * @param int $imagesCount
     * @param int $batchSize
     * @return \Closure
     */
    private function getFetchResultCallbackForBatches(int $imagesCount, int $batchSize): \Closure
    {
        $fetchResultsCallback = function () use (&$imagesCount, $batchSize) {
            $batchSize =
                ($imagesCount >= $batchSize) ? $batchSize : $imagesCount;
            $imagesCount -= $batchSize;

            $getFetchResults = function ($batchSize): array {
                $result = [];
                $count = $batchSize;
                while ($count) {
                    $count--;
                    $result[$count] = $count;
                }

                return $result;
            };

            return $getFetchResults($batchSize);
        };

        return $fetchResultsCallback;
    }

    /**
     * @param Select | MockObject $selectMock
     * @param int $batchCount
     * @return \Closure
     */
    private function getBatchIteratorCallback(MockObject $selectMock, int $batchCount): \Closure
    {
        $iteratorCallback = function () use ($batchCount, $selectMock): array {
            $result = [];
            $count = $batchCount;
            while ($count) {
                $count--;
                $result[$count] = $selectMock;
            }

            return $result;
        };

        return $iteratorCallback;
    }

    /**
     * Data Provider
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            [300, 300],
            [300, 100],
            [139, 100],
            [67, 10],
            [154, 47],
            [0, 100]
        ];
    }
}
