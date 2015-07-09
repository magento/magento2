<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadCustomCssTest extends \Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTest
{
    /** @var string */
    protected $name = 'DownloadCustomCss';

    public function testExecuteWithoutTheme()
    {
        $themeId = 23;
        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('theme_id')
            ->willReturn($themeId);

        $themeFactory = $this->getMock('Magento\Framework\View\Design\Theme\FlyweightFactory', [], [], '', false);
        $themeFactory->expects($this->once())
            ->method('create')
            ->with($themeId)
            ->willReturn(null);
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\View\Design\Theme\FlyweightFactory')
            ->willReturn($themeFactory);
        $this->messageManager->expects($this->once())
            ->method('addException');
        $this->redirect->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn('http:referer.url');
        $this->response
            ->expects($this->once())
            ->method('setRedirect')
            ->willReturn('http:referer.url');
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', [], '', false);
        $logger->expects($this->once())
            ->method('critical');
        $this->_objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Psr\Log\LoggerInterface')
            ->willReturn($logger);

        $this->_model->execute();
    }

    public function testExecute()
    {
        $themeId = 23;
        $customization = $this->getMock('Magento\Framework\View\Design\Theme\Customization', [], [], '', false);
        $themeFile = $this->getMockForAbstractClass('Magento\Framework\View\Design\Theme\FileInterface', [], '', false);

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('theme_id')
            ->willReturn($themeId);

        $theme = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\ThemeInterface',
            [],
            '',
            false,
            false,
            true,
            ['getCustomization']
        );

        $themeFile->expects($this->once())
            ->method('getContent')
            ->willReturn('Content');
        $themeFile->expects($this->once())
            ->method('getFileName')
            ->willReturn('file-name');
        $themeFile->expects($this->once())
            ->method('getFullPath')
            ->willReturn('/full/path/');
        $customization->expects($this->once())
            ->method('getFilesByType')
            ->with(\Magento\Theme\Model\Theme\Customization\File\CustomCss::TYPE)
            ->willReturn([$themeFile]);
        $theme->expects($this->once())
            ->method('getCustomization')
            ->willReturn($customization);
        $themeFactory = $this->getMock('Magento\Framework\View\Design\Theme\FlyweightFactory', [], [], '', false);
        $themeFactory->expects($this->once())
            ->method('create')
            ->with($themeId)
            ->willReturn($theme);
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\View\Design\Theme\FlyweightFactory')
            ->willReturn($themeFactory);
        $response = $this->getMockForAbstractClass('Magento\Framework\App\ResponseInterface', [], '', false);
        $this->fileFactory->expects($this->once())
            ->method('create')
            ->with(
                'file-name',
                ['type' => 'filename', 'value' => '/full/path/'],
                DirectoryList::ROOT
            )
            ->willReturn($response);

        $this->assertSame($response, $this->_model->execute());
    }
}
