<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Backend\Model\Menu;
use Magento\Framework\View\LayoutInterface;
use Magento\Theme\Block\Html\Title;
use Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTest;
use Psr\Log\LoggerInterface;

class IndexTest extends ThemeTest
{
    /**
     * @var string
     */
    protected $name = 'Index';

    public function testIndexAction()
    {
        $menuModel = $this->getMockBuilder(Menu::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(LoggerInterface::class)])
            ->getMock();
        $menuModel->expects($this->once())
            ->method('getParentItems')
            ->with('Magento_Theme::system_design_theme')
            ->willReturn([]);

        $menuBlock = $this->createMock(\Magento\Backend\Block\Menu::class);
        $menuBlock->expects($this->once())
            ->method('getMenuModel')
            ->willReturn($menuModel);

        $titleBlock = $this->createMock(Title::class);
        $titleBlock->expects($this->once())->method('setPageTitle');

        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
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
