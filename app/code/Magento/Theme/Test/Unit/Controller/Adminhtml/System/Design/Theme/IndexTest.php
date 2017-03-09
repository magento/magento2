<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $menuModel = $this->getMock(
            \Magento\Backend\Model\Menu::class,
            [],
            [$this->getMock(\Psr\Log\LoggerInterface::class)],
            '',
            false
        );
        $menuModel->expects($this->once())
            ->method('getParentItems')
            ->with($this->equalTo('Magento_Theme::system_design_theme'))
            ->will($this->returnValue([]));

        $menuBlock = $this->getMock(\Magento\Backend\Block\Menu::class, [], [], '', false);
        $menuBlock->expects($this->once())
            ->method('getMenuModel')
            ->will($this->returnValue($menuModel));

        $layout = $this->getMock(\Magento\Framework\View\LayoutInterface::class, [], [], '', false);
        $layout->expects($this->any())
            ->method('getBlock')
            ->with($this->equalTo('menu'))
            ->will($this->returnValue($menuBlock));

        $this->view->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layout));

        $this->_model->execute();
    }
}
