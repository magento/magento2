<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadCssTest extends \Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTest
{
    /** @var string  */
    protected $name = 'DownloadCss';

    public function testExecute()
    {
        $themeId = 23;
        $fileId = 'fileId';
        $assetSourceFile = '/source/file';
        $assetRelativePath = '/relative/path';

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('theme_id')
            ->willReturn($themeId);
        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('file')
            ->willReturn('file');

        $urlDecoder = $this->getMockForAbstractClass('Magento\Framework\Url\DecoderInterface', [], '', false);
        $urlDecoder->expects($this->once())
            ->method('decode')
            ->with('file')
            ->willReturn($fileId);
        $this->_objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Url\DecoderInterface')
            ->willReturn($urlDecoder);

        $theme = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\ThemeInterface',
            [],
            '',
            false,
            false,
            true,
            ['load', 'getId']
        );
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\View\Design\ThemeInterface')
            ->willReturn($theme);

        $asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $asset->expects($this->once())
            ->method('getSourceFile')
            ->willReturn($assetSourceFile);
        $this->assetRepo
            ->expects($this->once())
            ->method('createAsset')
            ->with($fileId, ['themeModel' => $theme])
            ->willReturn($asset);

        $directoryRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface', [], '', false);
        $directoryRead->expects($this->once())
            ->method('getRelativePath')
            ->with($assetSourceFile)
            ->willReturn($assetRelativePath);

        $this->appFileSystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($directoryRead);
        $response = $this->getMockForAbstractClass('Magento\Framework\App\ResponseInterface', [], '', false);

        $this->fileFactory->expects($this->once())
            ->method('create')
            ->with(
                $assetRelativePath,
                [
                    'type'  => 'filename',
                    'value' => $assetRelativePath
                ],
                DirectoryList::ROOT
            )
            ->willReturn($response);

        $this->assertSame($response, $this->_model->execute());
    }

    public function testExecuteWithException()
    {
        $themeId = 23;
        $fileId = 'fileId';
        $refererUrl = 'http://referer.url/';

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('theme_id')
            ->willReturn($themeId);
        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('file')
            ->willReturn('file');

        $urlDecoder = $this->getMockForAbstractClass('Magento\Framework\Url\DecoderInterface', [], '', false);
        $urlDecoder->expects($this->once())
            ->method('decode')
            ->with('file')
            ->willReturn($fileId);
        $this->_objectManagerMock
            ->expects($this->at(0))
            ->method('get')
            ->with('Magento\Framework\Url\DecoderInterface')
            ->willReturn($urlDecoder);

        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\View\Design\ThemeInterface')
            ->willThrowException(new \Exception('Exception message'));

        $this->messageManager
            ->expects($this->once())
            ->method('addException');

        $this->redirect
            ->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $this->response->expects($this->once())
            ->method('setRedirect')
            ->with($refererUrl);

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', [], '', false);
        $logger->expects($this->once())
            ->method('critical');
        $this->_objectManagerMock
            ->expects($this->at(2))
            ->method('get')
            ->with('Psr\Log\LoggerInterface')
            ->willReturn($logger);

        $this->_model->execute();
    }
}
