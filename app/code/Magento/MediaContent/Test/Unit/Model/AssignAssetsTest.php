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
use Magento\MediaContent\Model\AssignAssets;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for the AssignAsset command.
 */
class AssignAssetsTest extends TestCase
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
    private const ENTITY_TYPE = 'entity_type';

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
     * @var AssignAssets
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
            AssignAssets::class,
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
            self::ENTITY_TYPE => $contentType,
            self::ENTITY_ID => $contentEntityId,
            self::FIELD => $contentField
        ];
        $this->adapterMock
            ->expects(self::once())
            ->method('insertMultiple')
            ->with(self::PREFIXED_TABLE_MEDIA_CONTENT_ASSET, [$saveData])
            ->willReturn(self::AFFECTED_ROWS);

        $this->assignAsset->execute(
            $this->getContentIdentity($contentType, $contentField, $contentEntityId),
            [
                $assetId
            ]
        );
    }

    /**
     * Tests with exception scenario for saving relation between media asset and media content.
     */
    public function testExceptionExecute(): void {
        $this->resourceConnectionMock->method('getConnection')
            ->willThrowException((new \Exception()));

        $this->loggerMock
            ->expects(self::once())
            ->method('critical')
            ->willReturnSelf();

        $this->expectException(CouldNotSaveException::class);
        $this->assignAsset->execute(
            $this->createMock(ContentIdentityInterface::class),
            [
                '42'
            ]
        );
    }

    /**
     * Get content identity mock
     *
     * @param string $type
     * @param string $field
     * @param string $id
     * @return MockObject|ContentIdentityInterface
     */
    private function getContentIdentity(string $type, string $field, string $id): MockObject
    {
        $contentIdentity = $this->createMock(ContentIdentityInterface::class);
        $contentIdentity->expects($this->once())
            ->method('getEntityId')
            ->willReturn($id);
        $contentIdentity->expects($this->once())
            ->method('getField')
            ->willReturn($field);
        $contentIdentity->expects($this->once())
            ->method('getEntityType')
            ->willReturn($type);

        return $contentIdentity;
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
