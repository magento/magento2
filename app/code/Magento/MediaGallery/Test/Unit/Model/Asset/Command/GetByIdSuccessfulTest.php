<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Asset\Command;

use Laminas\Db\Adapter\Driver\Pdo\Statement;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Model\Asset\Command\GetById;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test the GetById command successful scenario
 */
class GetByIdSuccessfulTest extends TestCase
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
     * @var Statement|MockObject
     */
    private $statementMock;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $resourceConnection = $this->createMock(ResourceConnection::class);
        $this->assetFactory = $this->createMock(AssetInterfaceFactory::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->getMediaAssetById = (new ObjectManager($this))->getObject(
            GetById::class,
            [
                'resourceConnection' => $resourceConnection,
                'assetFactory' => $this->assetFactory,
                'logger' =>  $logger,
            ]
        );
        $this->adapter = $this->getMockForAbstractClass(AdapterInterface::class);
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

        $mediaAssetStub = $this->getMockBuilder(AssetInterface::class)
            ->getMock();
        $this->assetFactory->expects($this->once())->method('create')->willReturn($mediaAssetStub);

        $this->assertEquals(
            $mediaAssetStub,
            $this->getMediaAssetById->execute(self::MEDIA_ASSET_STUB_ID)
        );
    }
}
