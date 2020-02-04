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
use Psr\Log\LoggerInterface;
use Zend\Db\Adapter\Driver\Pdo\Statement;

/**
 * Test the GetById command with exception during get media data
 */
class GetByIdExceptionOnGetDataTest extends \PHPUnit\Framework\TestCase
{
    private const MEDIA_ASSET_STUB_ID = 1;

    private const MEDIA_ASSET_DATA = ['id' => 1];

    /**
     * @var GetById|MockObject
     */
    private $getMediaAssetById;
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
     * @var Statement|MockObject
     */
    private $statementMock;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $resourceConnection = $this->createMock(ResourceConnection::class);
        $assetFactory = $this->createMock(AssetInterfaceFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->getMediaAssetById = (new ObjectManager($this))->getObject(
            GetById::class,
            [
                'resourceConnection' => $resourceConnection,
                'assetFactory' => $assetFactory,
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
     * Test an exception during the get asset data query.
     */
    public function testExceptionDuringGetMediaAssetData(): void
    {
        $this->statementMock->method('fetch')->willReturn(self::MEDIA_ASSET_DATA);
        $this->adapter->method('query')->willThrowException(new \Exception());

        $this->expectException(IntegrationException::class);
        $this->logger->expects($this->any())
            ->method('critical')
            ->willReturnSelf();

        $this->getMediaAssetById->execute(self::MEDIA_ASSET_STUB_ID);
    }
}
