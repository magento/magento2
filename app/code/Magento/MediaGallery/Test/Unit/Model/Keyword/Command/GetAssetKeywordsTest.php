<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Keyword\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaGallery\Model\Keyword\Command\GetAssetKeywords;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GetAssetKeywordsTest extends TestCase
{
    /**
     * @var GetAssetKeywords
     */
    private $sut;

    /**
     * @var ResourceConnection | MockObject
     */
    private $resourceConnectionStub;

    /**
     * @var KeywordInterfaceFactory | MockObject
     */
    private $assetKeywordFactoryStub;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->resourceConnectionStub = $this->createMock(ResourceConnection::class);
        $this->assetKeywordFactoryStub = $this->createMock(KeywordInterfaceFactory::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->sut = new GetAssetKeywords(
            $this->resourceConnectionStub,
            $this->assetKeywordFactoryStub,
            $this->loggerMock
        );
    }

    /**
     * Posive test for the main case
     *
     * @dataProvider casesProvider
     * @param array $databaseQueryResult
     * @param int $expectedNumberOfFoundKeywords
     */
    public function testFind(array $databaseQueryResult, int $expectedNumberOfFoundKeywords): void
    {
        $randomAssetId = 12345;
        $this->configureResourceConnectionStub($databaseQueryResult);
        $this->configureAssetKeywordFactoryStub();

        /** @var KeywordInterface[] $keywords */
        $keywords = $this->sut->execute($randomAssetId);

        $this->assertCount($expectedNumberOfFoundKeywords, $keywords);
    }

    /**
     * Data provider for testFind
     *
     * @return array
     */
    public static function casesProvider(): array
    {
        return [
            'not_found' => [[],0],
            'find_one_keyword' => [
                'databaseQueryResult' => [['id' => 1, 'keyword' => 'keywordRawData']],
                'expectedNumberOfFoundKeywords' => 1
            ],
            'find_several_keywords' => [
                'databaseQueryResult' => [
                    ['id' => 1, 'keyword'=> 'keywordRawData'],
                    ['id' => 2, 'keyword' => 'keywordRawData']
                ],
                'expectedNumberOfFoundKeywords' => 2
            ],
        ];
    }

    /**
     * Test case when an error occured during get data request.
     *
     * @throws IntegrationException
     */
    public function testNotFoundBecauseOfError(): void
    {
        $randomAssetId = 1;

        $this->resourceConnectionStub
            ->method('getConnection')
            ->willThrowException((new \Exception()));

        $this->expectException(IntegrationException::class);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->willReturnSelf();

        $this->sut->execute($randomAssetId);
    }

    /**
     * Very fragile and coupled to the implementation
     *
     * @param array $queryResult
     */
    private function configureResourceConnectionStub(array $queryResult): void
    {
        $statementMock = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)->getMock();
        $statementMock
            ->method('fetchAll')
            ->willReturn($queryResult);

        $selectStub = $this->createMock(Select::class);
        $selectStub->method('from')->willReturnSelf();
        $selectStub->method('join')->willReturnSelf();
        $selectStub->method('where')->willReturnSelf();

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $connectionMock
            ->method('select')
            ->willReturn($selectStub);
        $connectionMock
            ->method('query')
            ->willReturn($statementMock);

        $this->resourceConnectionStub
            ->method('getConnection')
            ->willReturn($connectionMock);
    }

    private function configureAssetKeywordFactoryStub(): void
    {
        $keywordStub = $this->getMockBuilder(KeywordInterface::class)
            ->getMock();
        $this->assetKeywordFactoryStub
            ->method('create')
            ->willReturn($keywordStub);
    }
}
