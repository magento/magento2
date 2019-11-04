<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Asset\Command;

use Magento\MediaGallery\Model\Asset\Command\DeleteByPath;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Test the DeleteByPath command model
 */
class DeleteByPathTest extends TestCase
{
    private const TABLE_NAME = 'media_gallery_asset';
    private const FILE_PATH = 'test-file-path/test.jpg';

    /**
     * @var DeleteByPath
     */
    private $deleteMediaAssetByPath;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapter;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $resourceConnection = $this->createMock(ResourceConnection::class);

        $this->deleteMediaAssetByPath = (new ObjectManager($this))->getObject(
            DeleteByPath::class,
            [
                'resourceConnection' => $resourceConnection,
                'logger' =>  $this->logger,
            ]
        );

        $this->adapter = $this->createMock(AdapterInterface::class);
        $resourceConnection->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->adapter);
        $resourceConnection->expects($this->once())
            ->method('getTableName')
            ->with(self::TABLE_NAME)
            ->willReturn('prefix_' . self::TABLE_NAME);
    }

    /**
     * Test delete media asset by path command
     */
    public function testSuccessfulDeleteByIdExecution(): void
    {
        $this->adapter->expects($this->once())
            ->method('delete')
            ->with('prefix_' . self::TABLE_NAME, ['path = ?' => self::FILE_PATH]);

        $this->deleteMediaAssetByPath->execute(self::FILE_PATH);
    }

    /**
     * Assume that delete action will thrown an Exception
     */
    public function testExceptionOnDeleteExecution(): void
    {
        $this->adapter->expects($this->once())
            ->method('delete')
            ->with('prefix_' . self::TABLE_NAME, ['path = ?' => self::FILE_PATH])
            ->willThrowException(new \Exception());

        $this->expectException(CouldNotDeleteException::class);
        $this->logger->expects($this->once())
            ->method('critical')
            ->willReturnSelf();
        $this->deleteMediaAssetByPath->execute(self::FILE_PATH);
    }
}
