<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files */
    protected $controller;

    /** @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $view;

    protected function setUp()
    {
        $this->view = $this->getMock(\Magento\Framework\App\ViewInterface::class, [], [], '', false);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $helper->getObject(
            \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\Index::class,
            [
                'view' => $this->view,
            ]
        );
    }

    public function testExecute()
    {
        $this->view ->expects($this->once())
            ->method('loadLayout')
            ->with('overlay_popup');
        $this->view ->expects($this->once())
            ->method('renderLayout');

        $this->controller->execute();
    }
}
