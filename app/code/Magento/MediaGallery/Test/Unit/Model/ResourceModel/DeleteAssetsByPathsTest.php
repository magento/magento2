<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaGallery\Model\ResourceModel\DeleteAssetsByPaths;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeleteAssetsByPathsTest extends TestCase
{
    private const TABLE_NAME = 'media_gallery_asset';

    /**
     * @var DeleteAssetsByPaths
     */
    private $deleteAssetsByPaths;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapter;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var \Zend_Db_Statement_Interface|MockObject
     */
    private $statement;

    /**
     * When deleting an asset by path with mixed case, the asset with exact same path should be deleted
     *
     * @dataProvider assetDeleteByPathDataProvider
     * @throws CouldNotDeleteException
     */
    public function testDeleteCorrectAssetByPathWithCaseSensitiveMatches(
        array  $assets,
        string $assetPathToDelete,
        int    $assetIdToAssert
    ): void {
        $this->adapter->expects($this->once())->method('select')->willReturn($this->select);
        $this->select->expects($this->once())->method('from')->willReturnSelf();
        $this->select->expects($this->once())->method('where')->willReturnSelf();
        $this->adapter
            ->expects($this->once())
            ->method('query')
            ->with($this->select)
            ->willReturn($this->statement);
        $this->statement->expects($this->once())->method('fetchAll')->willReturn($assets);

        $this->adapter->expects($this->once())
            ->method('delete')
            ->with(self::TABLE_NAME, ['id = ?' => $assetIdToAssert]);

        $this->deleteAssetsByPaths->execute([$assetPathToDelete]);
    }

    protected function setUp(): void
    {
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $resourceConnection = $this->createMock(ResourceConnection::class);

        $this->deleteAssetsByPaths = new DeleteAssetsByPaths(
            $resourceConnection,
            $logger
        );

        $this->adapter = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->select = $this->createMock(Select::class);
        $this->statement = $this->createMock(\Zend_Db_Statement_Interface::class);

        $resourceConnection->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);

        $resourceConnection->expects($this->any())
            ->method('getTableName')
            ->willReturn(self::TABLE_NAME);
    }

    public static function assetDeleteByPathDataProvider(): array
    {
        return [
            [
                'assets' => self::getAssets(),
                'assetPathToDelete' => 'catalog/category/folder/image.jpg',
                'assetIdToAssert' => 1
            ],
            [
                'assets' => self::getAssets(),
                'assetPathToDelete' => 'catalog/category/folder/Image.jpg',
                'assetIdToAssert' => 2
            ],
            [
                'assets' => self::getAssets(),
                'assetPathToDelete' => 'catalog/category/folder/IMAGE.JPG',
                'assetIdToAssert' => 3
            ],
            [
                'assets' => self::getAssets(),
                'assetPathToDelete' => 'catalog/category/FOLDER',
                'assetIdToAssert' => 4
            ],
        ];
    }

    private static function getAssets(): array
    {
        return [
            [
                'id' => '1',
                'path' => 'catalog/category/folder/image.jpg',
                'title' => 'image',
                'description' => null,
                'source' => 'Local',
                'hash' => '20b88741b3cfa5749d414a0312c8b909aefbaa1f',
                'content_type' => 'image/jpg',
                'width' => '1080',
                'height' => '1080',
                'size' => '53010',
                'created_at' => '2023-11-09 16:33:41',
                'updated_at' => '2023-11-09 16:33:41',
            ],
            [
                'id' => '2',
                'path' => 'catalog/category/folder/Image.jpg',
                'title' => 'Image',
                'description' => null,
                'source' => 'Local',
                'hash' => '20b88741b3cfa5749d414a0312c8b909aefbaa1f',
                'content_type' => 'image/jpg',
                'width' => '1080',
                'height' => '1080',
                'size' => '53010',
                'created_at' => '2023-11-09 16:34:19',
                'updated_at' => '2023-11-09 16:34:19',
            ],
            [
                'id' => '3',
                'path' => 'catalog/category/folder/IMAGE.JPG',
                'title' => 'IMAGE',
                'description' => null,
                'source' => 'Local',
                'hash' => '93a7c1f07373afafcd4918379dacf8e3de6a3eca',
                'content_type' => 'image/jpg',
                'width' => '1080',
                'height' => '1080',
                'size' => '101827',
                'created_at' => '2023-11-09 16:37:36',
                'updated_at' => '2023-11-09 16:37:36',
            ],
            [
                'id' => '4',
                'path' => 'catalog/category/FOLDER/IMAGE.JPG',
                'title' => 'IMAGE',
                'description' => null,
                'source' => 'Local',
                'hash' => '93a7c1f07373afafcd4918379dacf8e3de6a3eca',
                'content_type' => 'image/jpg',
                'width' => '1080',
                'height' => '1080',
                'size' => '101827',
                'created_at' => '2023-11-09 16:37:36',
                'updated_at' => '2023-11-09 16:37:36',
            ]
        ];
    }
}
