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
            ->will($this->returnValue([]));

        $menuBlock = $this->createMock(\Magento\Backend\Block\Menu::class);
        $menuBlock->expects($this->once())
            ->method('getMenuModel')
            ->will($this->returnValue($menuModel));

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
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
