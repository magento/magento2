<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
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
            'Magento\Backend\Model\Menu',
            [],
            [$this->getMock('Psr\Log\LoggerInterface')]
        );
        $menuModel->expects($this->once())
            ->method('getParentItems')
            ->with($this->equalTo('Magento_Theme::system_design_theme'))
            ->will($this->returnValue([]));

        $menuBlock = $this->getMock('\Magento\Backend\Block\Menu', [], [], '', false);
        $menuBlock->expects($this->once())
            ->method('getMenuModel')
            ->will($this->returnValue($menuModel));

        $layout = $this->getMock('\Magento\Framework\View\LayoutInterface', [], [], '', false);
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
