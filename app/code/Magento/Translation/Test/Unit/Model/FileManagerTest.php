<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

    protected function setUp()
    {
        $this->assetRepoMock = $this->getMock('\Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->model = new FileManager($this->assetRepoMock);
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
}
