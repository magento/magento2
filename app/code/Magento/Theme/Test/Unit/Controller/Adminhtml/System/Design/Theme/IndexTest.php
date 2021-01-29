<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

class IndexTest extends \Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTest
{
    /**
     * @var string
     */
    protected $name = 'Index';

    public function testIndexAction()
    {
        $menuModel = $this->getMockBuilder(\Magento\Backend\Model\Menu::class)
            ->setConstructorArgs([$this->createMock(\Psr\Log\LoggerInterface::class)])
            ->getMock();
        $menuModel->expects($this->once())
            ->method('getParentItems')
            ->with($this->equalTo('Magento_Theme::system_design_theme'))
            ->willReturn([]);

        $menuBlock = $this->createMock(\Magento\Backend\Block\Menu::class);
        $menuBlock->expects($this->once())
            ->method('getMenuModel')
            ->willReturn($menuModel);

        $titleBlock = $this->createMock(\Magento\Theme\Block\Html\Title::class);
        $titleBlock->expects($this->once())->method('setPageTitle');

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $layout->expects($this->any())
            ->method('getBlock')
            ->willReturnMap([
                ['menu', $menuBlock],
                ['page.title', $titleBlock]
            ]);

        $this->view->expects($this->any())
            ->method('getLayout')
            ->willReturn($layout);

        $this->_model->execute();
    }
}
