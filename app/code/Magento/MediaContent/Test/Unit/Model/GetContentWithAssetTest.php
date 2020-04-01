<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContent\Model\GetContentWithAsset;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for the GetContentWithAsset command.
 */
class GetContentWithAssetTest extends TestCase
{
    /**
     * Expected list of assets for the return statement.
     */
    private const EXPECTED_LIST_OF_ASSETS =
        [
            1234123 => [
                1234123,
                'cms_page',
                '1',
                'content',
            ]
        ];

    /**
     * @var ResourceConnection | MockObject
     */
    private $resourceConnectionStub;

    /**
     * @var LoggerInterface | MockObject
     */
    private $loggerMock;

    /**
     * @var GetContentWithAsset
     */
    private $getContentWithAsset;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resourceConnectionStub  = $this->createMock(ResourceConnection::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->getContentWithAsset = new GetContentWithAsset($this->resourceConnectionStub, $this->loggerMock);
    }

    /**
     * Test successful execution of the GetContentWithAsset::execute.
     */
    public function testSuccessfulGetContentWithAsset(): void
    {
        $assetId = 1234123;
        $this->configureResourceConnectionStub();
        $assetList = $this->getContentWithAsset->execute($assetId);

        $this->assertEquals(self::EXPECTED_LIST_OF_ASSETS, $assetList);
    }

    /**
     * Test GetContentWithAsset::execute with exception.
     */
    public function testGetContentWithAssetWithException(): void
    {
        $this->resourceConnectionStub->method('getConnection')->willThrowException((new \Exception()));
        $this->expectException(IntegrationException::class);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->willReturnSelf();

        $this->getContentWithAsset->execute(1);
    }

    /**
     * Configure resource connection for the command. Based on the current implementation.
     */
    private function configureResourceConnectionStub(): void
    {
        $selectStub = $this->createMock(Select::class);
        $selectStub->method('from')->willReturnSelf();
        $selectStub->method('where')->willReturnSelf();

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)->getMock();
        $connectionMock->expects($this->any())->method('select')->willReturn($selectStub);
        $connectionMock->expects($this->any())
            ->method('fetchAssoc')
            ->with($selectStub)
            ->willReturn(self::EXPECTED_LIST_OF_ASSETS);
        $this->resourceConnectionStub->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
    }
}
