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
use Magento\MediaContent\Model\GetContentWithAssets;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for the GetContentWithAsset command.
 */
class GetContentWithAssetsTest extends TestCase
{
    /**
     * @var ResourceConnection | MockObject
     */
    private $resourceConnectionStub;

    /**
     * @var LoggerInterface | MockObject
     */
    private $loggerMock;

    /**
     * @var GetContentWithAssets
     */
    private $getContentWithAsset;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $factory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resourceConnectionStub  = $this->createMock(ResourceConnection::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->factory = $this->createMock(ContentIdentityInterfaceFactory::class);
        $this->getContentWithAsset = new GetContentWithAssets(
            $this->factory,
            $this->resourceConnectionStub,
            $this->loggerMock
        );
    }

    /**
     * Test successful execution of the GetContentWithAsset::execute.
     */
    public function testSuccessfulGetContentWithAsset(): void
    {
        $assetId = 1234123;
        $contentIdentityData = [
            'entity_type' => 'catalog_product',
            'entity_id' => 42,
            'field' => 'desctiption'
        ];
        $this->configureResourceConnectionStub($contentIdentityData);

        $contentIdentity = $this->createMock(ContentIdentityInterface::class);
        $this->factory->expects($this->once())
            ->method('create')
            ->with(['data' => $contentIdentityData])
            ->willReturn($contentIdentity);

        $this->assertEquals([$contentIdentity], $this->getContentWithAsset->execute([$assetId]));
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

        $this->getContentWithAsset->execute([1]);
    }

    /**
     * Configure resource connection for the command. Based on the current implementation.
     *
     * @param array $contentIdentityData
     */
    private function configureResourceConnectionStub(array $contentIdentityData): void
    {
        $selectStub = $this->createMock(Select::class);
        $selectStub->method('from')->willReturnSelf();
        $selectStub->method('where')->willReturnSelf();

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)->getMock();
        $connectionMock->expects($this->any())->method('select')->willReturn($selectStub);
        $connectionMock->expects($this->any())
            ->method('fetchAssoc')
            ->with($selectStub)
            ->willReturn([$contentIdentityData]);
        $this->resourceConnectionStub->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
    }
}
