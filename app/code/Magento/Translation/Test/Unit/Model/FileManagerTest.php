<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Translation\Model\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileManagerTest extends TestCase
{
    /**
     * @var FileManager|MockObject
     */
    private $model;

    /**
     * @var Repository|MockObject
     */
    private $assetRepoMock;

    /**
     * @var DirectoryList|MockObject
     */
    private $directoryListMock;

    /**
     * @var File|MockObject
     */
    private $driverFileMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->assetRepoMock = $this->createMock(Repository::class);
        $this->directoryListMock = $this->createMock(DirectoryList::class);
        $this->driverFileMock = $this->createMock(File::class);

        $this->model = $objectManager->getObject(
            FileManager::class,
            [
                'assetRepo' => $this->assetRepoMock,
                'directoryList' => $this->directoryListMock,
                'driverFile' => $this->driverFileMock,
            ]
        );
    }

    public function testCreateTranslateConfigAsset()
    {
        $path = 'relative path';
        $expectedPath = $path . '/' . FileManager::TRANSLATION_CONFIG_FILE_NAME;
        $fileMock = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $contextMock = $this->getMockForAbstractClass(
            ContextInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getPath']
        );
        $this->assetRepoMock->expects($this->once())->method('getStaticViewFileContext')->willReturn($contextMock);
        $contextMock->expects($this->once())->method('getPath')->willReturn($path);
        $this->assetRepoMock
            ->expects($this->once())
            ->method('createArbitrary')
            ->with($expectedPath, '')
            ->willReturn($fileMock);

        $this->assertSame($fileMock, $this->model->createTranslateConfigAsset());
    }

    public function testGetTranslationFileTimestamp()
    {
        $path = 'path';
        $contextMock = $this->getMockForAbstractClass(
            ContextInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getPath']
        );
        $this->assetRepoMock->expects($this->atLeastOnce())
            ->method('getStaticViewFileContext')
            ->willReturn($contextMock);
        $contextMock->expects($this->atLeastOnce())->method('getPath')->willReturn($path);
        $this->directoryListMock->expects($this->atLeastOnce())->method('getPath')->willReturn($path);
        $this->driverFileMock->expects($this->once())
            ->method('isExists')
            ->with('path/path/js-translation.json')
            ->willReturn(true);
        $this->driverFileMock->expects($this->once())->method('stat')->willReturn(['mtime' => 1445736974]);
        $this->assertEquals(1445736974, $this->model->getTranslationFileTimestamp());
    }

    public function testGetTranslationFilePath()
    {
        $path = 'path';
        $contextMock = $this->getMockForAbstractClass(
            ContextInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getPath']
        );
        $this->assetRepoMock->expects($this->atLeastOnce())
            ->method('getStaticViewFileContext')
            ->willReturn($contextMock);
        $contextMock->expects($this->atLeastOnce())->method('getPath')->willReturn($path);
        $this->assertEquals($path, $this->model->getTranslationFilePath());
    }
}
