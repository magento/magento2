<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Test\Unit\Model;

use Magento\Translation\Model\FileManager;

class FileManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Translation\Model\FileManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $assetRepoMock;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit\Framework\MockObject\MockObject
     */
    private $directoryListMock;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit\Framework\MockObject\MockObject
     */
    private $driverFileMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->assetRepoMock = $this->createMock(\Magento\Framework\View\Asset\Repository::class);
        $this->directoryListMock = $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);
        $this->driverFileMock = $this->createMock(\Magento\Framework\Filesystem\Driver\File::class);

        $this->model = $objectManager->getObject(
            \Magento\Translation\Model\FileManager::class,
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
            \Magento\Framework\View\Asset\ContextInterface::class,
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
            \Magento\Framework\View\Asset\ContextInterface::class,
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
            \Magento\Framework\View\Asset\ContextInterface::class,
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
