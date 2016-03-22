<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Test\Unit\Model;

use Magento\Translation\Model\FileManager;

class FileManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Translation\Model\FileManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepoMock;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryListMock;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $driverFileMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->assetRepoMock = $this->getMock('\Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->directoryListMock = $this->getMock('\Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->driverFileMock = $this->getMock('\Magento\Framework\Filesystem\Driver\File', [], [], '', false);

        $this->model = $objectManager->getObject(
            '\Magento\Translation\Model\FileManager',
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
        $fileMock = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $contextMock = $this->getMockForAbstractClass(
            '\Magento\Framework\View\Asset\ContextInterface',
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
            '\Magento\Framework\View\Asset\ContextInterface',
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
            '\Magento\Framework\View\Asset\ContextInterface',
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
