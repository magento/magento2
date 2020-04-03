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
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaContent\Model\UnassignAssets;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for the UnassignAsset command.
 */
class UnassignAssetTest extends TestCase
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
     * @var UnassignAssets
     */
    private $unassignAsset;

    /**
     * Initialize basic test class mocks
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

        $this->unassignAsset = (new ObjectManager($this))->getObject(
            UnassignAssets::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'logger'             => $this->loggerMock
            ]
        );
    }

    /**
     * Test successful scenario for deleting relation between media asset and media content.
     *
     * @param int $assetId
     * @param string $contentType
     * @param string $contentEntityId
     * @param string $contentField
     * @dataProvider unassignAssetDataProvider
     * @return void
     */
    public function testSuccessfulUnassignAsset(
        int $assetId,
        string $contentType,
        string $contentEntityId,
        string $contentField
    ): void {
        $this->adapterMock->expects($this->once())
            ->method('delete')
            ->with(
                self::PREFIXED_TABLE_MEDIA_CONTENT_ASSET,
                [
                    self::ASSET_ID . ' IN (?)' => [$assetId],
                    self::TYPE . ' = ?' => $contentType,
                    self::ENTITY_ID . ' = ?' => $contentEntityId,
                    self::FIELD . ' = ?' => $contentField
                ]
            );

        $this->unassignAsset->execute(
            $this->getContentIdentity(
                $contentType,
                $contentField,
                $contentEntityId
            ),
            [
                $assetId
            ]
        );
    }

    /**
     * Test exception scenario for deleting relation between media asset and media content.
     */
    public function testUnassignAssetWithException(): void {
        $this->resourceConnectionMock->method('getConnection')
            ->willThrowException((new \Exception()));

        $this->expectException(CouldNotDeleteException::class);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->willReturnSelf();
        $this->unassignAsset->execute(
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
    public function unassignAssetDataProvider(): array
    {
        return [
            [
                18976345,
                'cms_page',
                '1',
                'content'
            ]
        ];
    }
}
