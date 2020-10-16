<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Asset\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Model\Asset\Command\Save;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests the Save model using PHPUnit
 */
class SaveTest extends TestCase
{
    /**
     * Constant for tablename of media gallery assets
     */
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    /**
     * Constant for prefixed tablename of media gallery assets
     */
    private const PREFIXED_TABLE_MEDIA_GALLERY_ASSET = 'prefix_' . self::TABLE_MEDIA_GALLERY_ASSET;

    /**
     * Constant for last ID generated after data insertion
     */
    private const INSERT_ID = '1';

    /**
     * Constant for affected rows count after data insertion
     */
    private const AFFECTED_ROWS = 1;

    /**
     * Constant for image data
     */
    private const IMAGE_DATA = [
        'id' => null,
        'path' => '/test/path',
        'title' => 'Test Title',
        'source' => 'Adobe Stock',
        'content_type' => 'image/jpeg',
        'height' => 4863,
        'width' => 12129,
        'size' => 300,
    ];

    /**
     * @var MockObject | ResourceConnection
     */
    private $resourceConnectionMock;

    /**
     * @var MockObject | LoggerInterface
     */
    private $loggerMock;

    /**
     * @var MockObject | DataObjectProcessor
     */
    private $objectProcessor;

    /**
     * @var MockObject | AdapterInterface
     */
    private $adapterMock;

    /**
     * @var MockObject | AssetInterface
     */
    private $mediaAssetMock;

    /**
     * @var Save
     */
    private $save;

    /**
     * Set up test mocks
     */
    protected function setUp(): void
    {
        /* Intermediary mocks */
        $this->adapterMock = $this->createMock(Mysql::class);
        $this->mediaAssetMock = $this->getMockForAbstractClass(AssetInterface::class);

        /* Save constructor mocks */
        $this->objectProcessor = $this->createMock(DataObjectProcessor::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->resourceConnectionMock = $this->createConfiguredMock(
            ResourceConnection::class,
            [
                'getConnection' => $this->adapterMock,
                'getTableName'  => self::PREFIXED_TABLE_MEDIA_GALLERY_ASSET
            ]
        );

        /* Create Save instance with mocks */
        $this->save = (new ObjectManager($this))->getObject(
            Save::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'objectProcessor'    => $this->objectProcessor,
                'logger'             => $this->loggerMock
            ]
        );
    }

    /**
     * Tests a successful Save::execute method
     */
    public function testSuccessfulExecute(): void
    {
        $this->resourceConnectionMock->expects(self::once())->method('getConnection');
        $this->resourceConnectionMock->expects(self::once())->method('getTableName');

        $this->mediaAssetMock->expects(self::once())->method('getId')->willReturn(self::IMAGE_DATA['id']);
        $this->mediaAssetMock->expects(self::once())->method('getPath')->willReturn(self::IMAGE_DATA['path']);
        $this->mediaAssetMock->expects(self::once())->method('getTitle')->willReturn(self::IMAGE_DATA['title']);
        $this->mediaAssetMock->expects(self::once())->method('getSource')->willReturn(self::IMAGE_DATA['source']);
        $this->mediaAssetMock->expects(self::once())->method('getWidth')->willReturn(self::IMAGE_DATA['width']);
        $this->mediaAssetMock->expects(self::once())->method('getHeight')->willReturn(self::IMAGE_DATA['height']);
        $this->mediaAssetMock->expects(self::once())->method('getSize')->willReturn(self::IMAGE_DATA['size']);
        $this->mediaAssetMock->expects(self::once())
            ->method('getContentType')
            ->willReturn(self::IMAGE_DATA['content_type']);

        $this->adapterMock
            ->expects(self::once())
            ->method('insertOnDuplicate')
            ->with(self::PREFIXED_TABLE_MEDIA_GALLERY_ASSET, self::IMAGE_DATA)
            ->willReturn(self::AFFECTED_ROWS);

        $this->adapterMock
            ->expects(self::once())
            ->method('lastInsertId')
            ->with(self::PREFIXED_TABLE_MEDIA_GALLERY_ASSET)
            ->willReturn(self::INSERT_ID);

        $this->save->execute($this->mediaAssetMock);
    }

    /**
     * Tests Save::execute method with an exception thrown
     */
    public function testExceptionExecute(): void
    {
        $this->resourceConnectionMock->expects(self::once())->method('getConnection');
        $this->resourceConnectionMock->expects(self::once())->method('getTableName');

        $this->mediaAssetMock->expects(self::once())->method('getId')->willReturn(self::IMAGE_DATA['id']);
        $this->mediaAssetMock->expects(self::once())->method('getPath')->willReturn(self::IMAGE_DATA['path']);
        $this->mediaAssetMock->expects(self::once())->method('getTitle')->willReturn(self::IMAGE_DATA['title']);
        $this->mediaAssetMock->expects(self::once())->method('getSource')->willReturn(self::IMAGE_DATA['source']);
        $this->mediaAssetMock->expects(self::once())->method('getWidth')->willReturn(self::IMAGE_DATA['width']);
        $this->mediaAssetMock->expects(self::once())->method('getHeight')->willReturn(self::IMAGE_DATA['height']);
        $this->mediaAssetMock->expects(self::once())->method('getSize')->willReturn(self::IMAGE_DATA['size']);
        $this->mediaAssetMock->expects(self::once())
            ->method('getContentType')
            ->willReturn(self::IMAGE_DATA['content_type']);

        $this->adapterMock
            ->expects(self::once())
            ->method('insertOnDuplicate')
            ->with(self::PREFIXED_TABLE_MEDIA_GALLERY_ASSET, self::IMAGE_DATA)
            ->willThrowException(new \Zend_Db_Exception());

        $this->loggerMock
            ->expects(self::once())
            ->method('critical')
            ->willReturnSelf();

        $this->expectException(CouldNotSaveException::class);

        $this->save->execute($this->mediaAssetMock);
    }
}
