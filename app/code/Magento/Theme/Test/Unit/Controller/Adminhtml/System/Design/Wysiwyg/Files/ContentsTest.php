<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class ContentsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files */
    protected $controller;

    /** @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $view;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\PHPUnit_Framework_MockObject_MockObject*/
    protected $objectManager;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    /** @var \Magento\Theme\Helper\Storage|\PHPUnit_Framework_MockObject_MockObject */
    protected $storage;

    protected function setUp()
    {
        $this->view = $this->getMock('\Magento\Framework\App\ViewInterface', [], [], '', false);
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->session = $this->getMock('Magento\Backend\Model\Session', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->storage = $this->getMock('Magento\Theme\Helper\Storage', [], [], '', false);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $helper->getObject(
            'Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\Contents',
            [
                'objectManager' => $this->objectManager,
                'view' => $this->view,
                'session' => $this->session,
                'response' => $this->response,
                'storage' => $this->storage
            ]
        );
    }

    public function testExecute()
    {
        $layout = $this->getMockForAbstractClass('Magento\Framework\View\LayoutInterface', [], '', false);
        $storage = $this->getMock('Magento\Theme\Model\Wysiwyg\Storage', [], [], '', false);
        $block = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\BlockInterface',
            [],
            '',
            false,
            false,
            true,
            ['setStorage']
        );

        $this->view->expects($this->once())
            ->method('loadLayout')
            ->with('empty');
        $this->view->expects($this->once())
            ->method('getLayout')
            ->willReturn($layout);
        $layout->expects($this->once())
            ->method('getBlock')
            ->with('wysiwyg_files.files')
            ->willReturn($block);
        $block->expects($this->once())
            ->method('setStorage')
            ->with($storage);
        $this->objectManager->expects($this->at(0))
            ->method('get')
            ->with('Magento\Theme\Model\Wysiwyg\Storage')
            ->willReturn($storage);
        $this->storage->expects($this->once())
            ->method('getCurrentPath')
            ->willThrowException(new \Exception('Message'));

        $jsonData = $this->getMock('Magento\Framework\Json\Helper\Data', [], [], '', false);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'Message'])
            ->willReturn('{"error":"true","message":"Message"}');

        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with('Magento\Framework\Json\Helper\Data')
            ->willReturn($jsonData);

        $this->response->expects($this->once())
            ->method('representJson');

        $this->controller->execute();
    }
}
