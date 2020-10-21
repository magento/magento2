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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Model\Asset\Command\GetById;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test the GetById command with exception during media asset initialization
 */
class GetByIdExceptionDuringMediaAssetInitializationTest extends TestCase
{
    private const MEDIA_ASSET_STUB_ID = 45;
    private const MEDIA_ASSET_DATA = [
        'id' => 45,
        'path' => 'img.jpg',
        'title' => 'Img',
        'description' => 'Img Description',
        'source' => 'Adobe Stock',
        'hash' => 'hash',
        'content_type' => 'image/jpeg',
        'width' => 420,
        'height' => 240,
        'size' => 12877,
        'created_at' => '2020',
        'updated_at' => '2020'
    ];

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
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->getMediaAssetById = (new ObjectManager($this))->getObject(
            GetById::class,
            [
                'resourceConnection' => $resourceConnection,
                'assetFactory' => $this->assetFactory,
                'logger' =>  $this->logger,
            ]
        );
        $this->adapter = $this->getMockForAbstractClass(AdapterInterface::class);
        $resourceConnection->method('getConnection')->willReturn($this->adapter);

        $this->selectStub = $this->createMock(Select::class);
        $this->selectStub->method('from')->willReturnSelf();
        $this->selectStub->method('where')->willReturnSelf();
        $this->adapter->method('select')->willReturn($this->selectStub);
    }

    /**
     * Test case when a problem occurred during asset initialization from received data.
     */
    public function testErrorDuringMediaAssetInitializationException(): void
    {
        $statementMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $statementMock->method('fetch')
            ->willReturn(self::MEDIA_ASSET_DATA);
        $this->adapter->method('query')->willReturn($statementMock);

        $this->assetFactory->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception());

        $this->expectException(IntegrationException::class);
        $this->logger->expects($this->any())
            ->method('critical')
            ->willReturnSelf();

        $this->getMediaAssetById->execute(self::MEDIA_ASSET_STUB_ID);
    }
}
