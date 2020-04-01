<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaContent\Model\AssignAsset;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for the AssignAsset command.
 */
class AssignAssetTest extends TestCase
{
    /**
     * Media content relation data storage table name
     */
    private const TABLE_MEDIA_CONTENT_ASSET = 'media_content_asset';

    /**
     * Prefixed media content relation data storage table name
     */
    private const PREFIXED_TABLE_MEDIA_CONTENT_ASSET = 'prefix_' . self::TABLE_MEDIA_CONTENT_ASSET;

    /**
     * Media asset id
     */
    private const ASSET_ID = 'asset_id';

    /**
     * Media content type
     */
    private const TYPE = 'type';

    /**
     * Media entity id
     */
    private const ENTITY_ID = 'entity_id';

    /**
     * Media content field where media asset is used
     */
    private const FIELD = 'field';

    /**
     * Constant for affected rows count after data insertion
     */
    private const AFFECTED_ROWS = 1;

    /**
     * @var ResourceConnection | MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface | MockObject
     */
    private $adapterMock;

    /**
     * @var LoggerInterface | MockObject
     */
    private $loggerMock;

    /**
     * @var AssignAsset
     */
    private $assignAsset;

    /**
     * Set up test mocks
     */
    protected function setUp(): void
    {
        $this->adapterMock = $this->createMock(Mysql::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->resourceConnectionMock = $this->createConfiguredMock(
            ResourceConnection::class,
            [
                'getConnection' => $this->adapterMock,
                'getTableName'  => self::PREFIXED_TABLE_MEDIA_CONTENT_ASSET
            ]
        );

        $this->assignAsset = (new ObjectManager($this))->getObject(
            AssignAsset::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'logger'             => $this->loggerMock
            ]
        );
    }

    /**
     * Tests successful scenario for saving relation between media asset and media content.
     *
     * @param int $assetId
     * @param string $contentType
     * @param string $contentEntityId
     * @param string $contentField
     * @dataProvider assignAssetDataProvider
     * @return void
     */
    public function testSuccessfulExecute(
        int $assetId,
        string $contentType,
        string $contentEntityId,
        string $contentField
    ): void {
        $saveData = [
            self::ASSET_ID => $assetId,
            self::TYPE => $contentType,
            self::ENTITY_ID => $contentEntityId,
            self::FIELD => $contentField
        ];
        $this->adapterMock
            ->expects(self::once())
            ->method('insert')
            ->with(self::PREFIXED_TABLE_MEDIA_CONTENT_ASSET, $saveData)
            ->willReturn(self::AFFECTED_ROWS);

        $this->assignAsset->execute($assetId, $contentType, $contentEntityId, $contentField);
    }

    /**
     * Tests with exception scenario for saving relation between media asset and media content.
     *
     * @param int $assetId
     * @param string $contentType
     * @param string $contentEntityId
     * @param string $contentField
     * @dataProvider assignAssetDataProvider
     */
    public function testExceptionExecute(
        int $assetId,
        string $contentType,
        string $contentEntityId,
        string $contentField
    ): void {
        $this->resourceConnectionMock->method('getConnection')->willThrowException((new \Exception()));

        $this->loggerMock
            ->expects(self::once())
            ->method('critical')
            ->willReturnSelf();

        $this->expectException(CouldNotSaveException::class);
        $this->assignAsset->execute($assetId, $contentType, $contentEntityId, $contentField);
    }

    /**
     * Media asset to media content relation data
     *
     * @return array
     */
    public function assignAssetDataProvider(): array
    {
        return [
            [
                '18976345',
                'cms_page',
                '1',
                'content'
            ]
        ];
    }
}
