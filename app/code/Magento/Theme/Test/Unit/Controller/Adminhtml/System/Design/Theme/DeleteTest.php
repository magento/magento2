<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

class DeleteTest extends \Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTest
{
    /**
     * @var string
     */
    protected $name = 'Delete';

    public function testExecuteWithoutLoadedTheme()
    {
        $themeId = 23;
        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

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
            ->willReturn(null);

        $this->_objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\View\Design\ThemeInterface')
            ->willReturn($theme);
        
        $this->messageManager->expects($this->once())
            ->method('addException');

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', [], '', false);
        $logger->expects($this->once())
            ->method('critical');
        $this->_objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with('Psr\Log\LoggerInterface')
            ->willReturn($logger);

        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('back', false)
            ->willReturn(true);

        $result = $this->getMockForAbstractClass(
            'Magento\Framework\Controller\ResultInterface',
            [],
            '',
            false,
            false,
            true,
            ['setPath']
        );
        $result->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/system_design_editor/index/')
            ->willReturnSelf();
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->willReturn($result);

        $this->assertSame($result, $this->_model->execute());
    }

    public function testExecuteWithNotVirtualTheme()
    {
        $themeId = 23;
        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $theme = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\ThemeInterface',
            [],
            '',
            false,
            false,
            true,
            ['load', 'getId', 'isVirtual']
        );
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $theme->expects($this->once())
            ->method('isVirtual')
            ->willReturn(false);

        $this->_objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\View\Design\ThemeInterface')
            ->willReturn($theme);

        $this->messageManager->expects($this->once())
            ->method('addException');

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', [], '', false);
        $logger->expects($this->once())
            ->method('critical');
        $this->_objectManagerMock
            ->expects($this->once())
            ->method('get')
            ->with('Psr\Log\LoggerInterface')
            ->willReturn($logger);

        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('back', false)
            ->willReturn(true);

        $result = $this->getMockForAbstractClass(
            'Magento\Framework\Controller\ResultInterface',
            [],
            '',
            false,
            false,
            true,
            ['setPath']
        );
        $result->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/system_design_editor/index/')
            ->willReturnSelf();
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->willReturn($result);

        $this->assertSame($result, $this->_model->execute());
    }

    public function testExecute()
    {
        $themeId = 23;
        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $theme = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\ThemeInterface',
            [],
            '',
            false,
            false,
            true,
            ['load', 'getId', 'isVirtual', 'delete']
        );
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $theme->expects($this->once())
            ->method('isVirtual')
            ->willReturn(true);

        $this->_objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\View\Design\ThemeInterface')
            ->willReturn($theme);

        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with('You deleted the theme.')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('Exception message')
                )
            );
        $this->messageManager->expects($this->once())
            ->method('addError');

        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('back', false)
            ->willReturn(false);

        $result = $this->getMockForAbstractClass(
            'Magento\Framework\Controller\ResultInterface',
            [],
            '',
            false,
            false,
            true,
            ['setPath']
        );
        $result->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/')
            ->willReturnSelf();
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->willReturn($result);

        $this->assertSame($result, $this->_model->execute());
    }
}
