<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
            ->setConstructorArgs([$this->createMock(LoggerInterface::class)])
            ->getMock();
        $menuModel->expects($this->once())
            ->method('getParentItems')
            ->with($this->equalTo('Magento_Theme::system_design_theme'))
            ->will($this->returnValue([]));

        $menuBlock = $this->createMock(\Magento\Backend\Block\Menu::class);
        $menuBlock->expects($this->once())
            ->method('getMenuModel')
            ->will($this->returnValue($menuModel));

        $titleBlock = $this->createMock(Title::class);
        $titleBlock->expects($this->once())->method('setPageTitle');

        $layout = $this->createMock(LayoutInterface::class);
        $layout->expects($this->any())
            ->method('getBlock')
            ->willReturnMap([
                ['menu', $menuBlock],
                ['page.title', $titleBlock]
            ]);

        $this->view->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($layout));

        $this->_model->execute();
    }
}
