<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Asset\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Model\Asset\Command\GetById;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Zend\Db\Adapter\Driver\Pdo\Statement;

/**
 * Test the GetById command model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetByIdTest extends TestCase
{
    private const MEDIA_ASSET_STUB_ID = 1;

    private const MEDIA_ASSET_DATA = ['id' => 1];

    /**
     * @var GetById|MockObject
     */
    private $getMediaAssetById;

    /**
     * @var AssetInterfaceFactory|MockObject
     */
    private $assetFactory;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapter;

    /**
     * @var Select|MockObject
     */
    private $selectStub;

    /**
     * @var Statement|MockObject
     */
    private $statementMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $resourceConnection = $this->createMock(ResourceConnection::class);
        $this->assetFactory = $this->createMock(AssetInterfaceFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->getMediaAssetById = (new ObjectManager($this))->getObject(
            GetById::class,
            [
                'resourceConnection' => $resourceConnection,
                'assetFactory' => $this->assetFactory,
                'logger' =>  $this->logger,
            ]
        );
        $this->adapter = $this->createMock(AdapterInterface::class);
        $resourceConnection->method('getConnection')->willReturn($this->adapter);

        $this->selectStub = $this->createMock(Select::class);
        $this->selectStub->method('from')->willReturnSelf();
        $this->selectStub->method('where')->willReturnSelf();
        $this->adapter->method('select')->willReturn($this->selectStub);

        $this->statementMock = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)->getMock();
    }

    /**
     * Test successful get media asset by id command execution.
     */
    public function testSuccessfulGetByIdExecution(): void
    {
        $this->statementMock->method('fetch')->willReturn(self::MEDIA_ASSET_DATA);
        $this->adapter->method('query')->willReturn($this->statementMock);

        $mediaAssetStub = $this->getMockBuilder(AssetInterface::class)->getMock();
        $this->assetFactory->expects($this->once())->method('create')->willReturn($mediaAssetStub);

        $this->assertEquals(
            $mediaAssetStub,
            $this->getMediaAssetById->execute(self::MEDIA_ASSET_STUB_ID)
        );
    }

    /**
     * Test an exception during the get asset data query.
     */
    public function testExceptionDuringGetMediaAssetData(): void
    {
        $this->statementMock->method('fetch')->willReturn(self::MEDIA_ASSET_DATA);
        $this->adapter->method('query')->willThrowException(new \Exception());

        $this->expectException(IntegrationException::class);

        $this->getMediaAssetById->execute(self::MEDIA_ASSET_STUB_ID);
    }

    /**
     * Test case when there is no found media asset by id.
     */
    public function testNotFoundMediaAssetException(): void
    {
        $this->statementMock->method('fetch')->willReturn([]);
        $this->adapter->method('query')->willReturn($this->statementMock);

        $this->expectException(NoSuchEntityException::class);

        $this->getMediaAssetById->execute(self::MEDIA_ASSET_STUB_ID);
    }

    /**
     * Test case when a problem occurred during asset initialization from received data.
     */
    public function testErrorDuringMediaAssetInitializationException(): void
    {
        $this->statementMock->method('fetch')->willReturn(self::MEDIA_ASSET_DATA);
        $this->adapter->method('query')->willReturn($this->statementMock);

        $this->assetFactory->expects($this->once())->method('create')->willThrowException(new \Exception());

        $this->expectException(IntegrationException::class);
        $this->logger->expects($this->any())
            ->method('critical')
            ->willReturnSelf();

        $this->getMediaAssetById->execute(self::MEDIA_ASSET_STUB_ID);
    }
}
