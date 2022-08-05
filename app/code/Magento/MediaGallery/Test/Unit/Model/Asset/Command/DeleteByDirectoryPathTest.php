<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Asset\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Model\Asset\Command\DeleteByDirectoryPath;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test the DeleteByDirectoryPath command model
 */
class DeleteByDirectoryPathTest extends TestCase
{
    private const TABLE_NAME = 'media_gallery_asset';
    private const DIRECTORY_PATH = 'test-directory-path/';

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var DeleteByDirectoryPath
     */
    private $deleteMediaAssetByDirectoryPath;

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
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);

        $this->deleteMediaAssetByDirectoryPath = (new ObjectManager($this))->getObject(
            DeleteByDirectoryPath::class,
            [
                'resourceConnection' => $this->resourceConnection,
                'logger' =>  $this->logger,
            ]
        );

        $this->adapter = $this->getMockForAbstractClass(AdapterInterface::class);
    }

    /**
     * Test delete media asset by path command
     *
     * @param string $directoryPath
     * @throws CouldNotDeleteException
     * @dataProvider directoryPathDataProvider
     */
    public function testDeleteByDirectoryPath(string $directoryPath): void
    {
        if (!empty($directoryPath)) {
            $this->resourceConnection->expects($this->once())
                ->method('getConnection')
                ->willReturn($this->adapter);
            $this->resourceConnection->expects($this->once())
                ->method('getTableName')
                ->with(self::TABLE_NAME)
                ->willReturn('prefix_' . self::TABLE_NAME);
            $this->adapter->expects($this->once())
                ->method('delete')
                ->with('prefix_' . self::TABLE_NAME, ['path LIKE ?' => self::DIRECTORY_PATH . '%']);
        } else {
            self::expectException('\Magento\Framework\Exception\CouldNotDeleteException');
        }

        $this->deleteMediaAssetByDirectoryPath->execute($directoryPath);
    }

    /**
     * Data provider for directory path
     *
     * @return array
     */
    public function directoryPathDataProvider(): array
    {
        return [
            'Existing path' => [self::DIRECTORY_PATH],
            'Empty path' => ['']
        ];
    }
}
