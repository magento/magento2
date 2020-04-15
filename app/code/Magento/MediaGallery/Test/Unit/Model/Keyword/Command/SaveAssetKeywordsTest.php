<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Keyword\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaGallery\Model\Keyword\Command\SaveAssetKeywords;
use Magento\MediaGallery\Model\Keyword\Command\SaveAssetLinks;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * SaveAssetKeywordsTest.
 */
class SaveAssetKeywordsTest extends TestCase
{
    /**
     * @var SaveAssetKeywords
     */
    private $sut;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var Mysql|MockObject
     */
    private $connectionMock;

    /**
     * @var SaveAssetLinks|MockObject
     */
    private $saveAssetLinksMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * SetUp
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->saveAssetLinksMock = $this->createMock(SaveAssetLinks::class);
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock = $this->createMock(Select::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->sut = new SaveAssetKeywords(
            $this->resourceConnectionMock,
            $this->saveAssetLinksMock,
            $this->loggerMock
        );
    }

    /**
     * Test saving the asset keywords
     *
     * @dataProvider assetKeywordsDataProvider
     *
     * @param array $keywords
     * @param int $assetId
     * @param array $items
     */
    public function testAssetKeywordsSave(array $keywords, int $assetId, array $items): void
    {
        $expectedCalls = (int) (count($keywords));

        if ($expectedCalls) {
            $this->prepareResourceConnection();
            $this->connectionMock->expects($this->once())
                ->method('insertArray')
                ->with(
                    'prefix_media_gallery_keyword',
                    ['keyword'],
                    $items,
                    2
                );
        }

        $this->sut->execute($keywords, $assetId);
    }

    /**
     * Testing throwing exception handling
     *
     * @throws CouldNotSaveException
     */
    public function testAssetNotSavingCausedByError(): void
    {
        $keyword = new DataObject(['keyword' => 'keyword-1']);

        $this->resourceConnectionMock
            ->method('getConnection')
            ->willThrowException((new \Exception()));
        $this->expectException(CouldNotSaveException::class);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->willReturnSelf();

        $this->sut->execute([$keyword], 1);
    }

    /**
     * Preparing the resource connection
     */
    private function prepareResourceConnection(): void
    {
        $this->selectMock->method('from')->willReturnSelf();
        $this->selectMock->method('columns')->with('id')->willReturnSelf();
        $this->selectMock->method('where')->willReturnSelf();

        $this->connectionMock
            ->method('select')
            ->willReturn($this->selectMock);
        $this->connectionMock
            ->method('fetchCol')
            ->willReturn([['id'=> 1], ['id' => 2]]);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->with('media_gallery_keyword')
            ->willReturn('prefix_media_gallery_keyword');
    }

    /**
     * Providing asset keywords
     *
     * @return array
     */
    public function assetKeywordsDataProvider(): array
    {
        return [
            [
                [],
                1,
                []
            ], [
                [
                    new DataObject(['keyword' => 'keyword-1']),
                    new DataObject(['keyword' => 'keyword-2']),
                ],
                1,
                ['keyword-1', 'keyword-2']
            ]
        ];
    }
}
