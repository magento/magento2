<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\ResourceModel\Keyword;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaGallery\Model\ResourceModel\Keyword\SaveAssetLinks;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SaveAssetLinksTest extends TestCase
{
    /**
     * @var SaveAssetLinks
     */
    private $sut;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var GetAssetsKeywordsInterface
     */
    private $getAssetsKeywords;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->getAssetsKeywords = $this->getMockForAbstractClass(GetAssetsKeywordsInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->sut = new SaveAssetLinks(
            $this->getAssetsKeywords,
            $this->resourceConnectionMock,
            $this->loggerMock
        );
    }

    /**
     * Test saving the asset keyword links
     *
     * @dataProvider assetLinksDataProvider
     *
     * @param int $assetId
     * @param array $keywordIds
     * @param array $values
     * @throws CouldNotSaveException
     */
    public function testAssetKeywordsSave(int $assetId, array $keywordIds, array $values): void
    {
        $expectedCalls = (int) (count($keywordIds));

        if ($expectedCalls) {
            $this->resourceConnectionMock->expects($this->exactly(2))
                ->method('getConnection')
                ->willReturn($this->connectionMock);
            $this->resourceConnectionMock->expects($this->any())
                ->method('getTableName')
                ->willReturnMap(
                    [
                        ['media_gallery_asset_keyword', 'default', 'prefix_media_gallery_asset_keyword'],
                        ['media_gallery_asset', 'default', 'prefix_media_gallery_asset']
                    ]
                );
            $this->connectionMock->expects($this->once())
                ->method('insertArray')
                ->with(
                    'prefix_media_gallery_asset_keyword',
                    ['asset_id', 'keyword_id'],
                    $values,
                    2
                );
        }

        $this->sut->execute($assetId, $keywordIds);
    }

    /**
     * Testing throwing exception handling
     *
     * @throws CouldNotSaveException
     */
    public function testAssetNotSavingCausedByError(): void
    {
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('insertArray')
            ->willThrowException((new \Exception()));
        $this->expectException(CouldNotSaveException::class);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->willReturnSelf();

        $this->sut->execute(1, [1, 2]);
    }

    /**
     * Providing asset links
     *
     * @return array
     */
    public static function assetLinksDataProvider(): array
    {
        return [
            [
                12,
                [],
                []
            ],
            [
                12,
                [1],
                [
                    [12, 1]
                ]
            ], [
                12,
                [1, 2],
                [
                    [12, 1],
                    [12, 2],
                ]
            ]
        ];
    }
}
