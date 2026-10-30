<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Image;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImageRelatedWebsiteIdsTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Image
     */
    private $imageModel;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $resourceMock = $this->createMock(ResourceConnection::class);
        $resourceMock->method('getConnection')->willReturn($this->connectionMock);
        $resourceMock->method('getTableName')->willReturnArgument(0);
        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $metadataPoolMock->method('getMetadata')
            ->willReturn($this->createMock(EntityMetadataInterface::class));

        $this->imageModel = new Image(
            $this->createMock(Generator::class),
            $resourceMock,
            $metadataPoolMock,
        );
    }

    /**
     * Website ids of every product sharing the image must be returned, not only the last one's.
     */
    public function testGetRelatedWebsiteIds(): void
    {
        $filepath = 'm/y/image.jpg';
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('distinct')->willReturnSelf();
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('joinInner')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $selectMock->method('group')->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        // One row per product sharing the image; website ids overlap across products.
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($selectMock)
            ->willReturn([
                ['filepath' => $filepath, 'website_ids' => '1,2'],
                ['filepath' => $filepath, 'website_ids' => '2,3'],
            ]);

        $this->assertSame([1, 2, 3], $this->imageModel->getRelatedWebsiteIds($filepath));
    }
}
