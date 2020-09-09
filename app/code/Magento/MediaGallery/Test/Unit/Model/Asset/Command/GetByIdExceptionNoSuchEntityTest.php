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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Model\Asset\Command\GetById;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test the GetById command with exception thrown in case when there is no such entity
 */
class GetByIdExceptionNoSuchEntityTest extends TestCase
{
    private const MEDIA_ASSET_STUB_ID = 1;

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
     * Test case when there is no found media asset by id.
     */
    public function testNotFoundMediaAssetException(): void
    {
        $this->statementMock->method('fetch')->willReturn([]);
        $this->adapter->method('query')->willReturn($this->statementMock);

        $this->expectException(NoSuchEntityException::class);

        $this->getMediaAssetById->execute(self::MEDIA_ASSET_STUB_ID);
    }
}
