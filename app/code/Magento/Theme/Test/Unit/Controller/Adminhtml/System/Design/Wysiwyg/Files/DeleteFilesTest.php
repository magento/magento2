<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class DeleteFilesTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files */
    protected $controller;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\PHPUnit_Framework_MockObject_MockObject*/
    protected $objectManager;

    /** @var \Magento\Theme\Helper\Storage|\PHPUnit_Framework_MockObject_MockObject */
    protected $storage;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    protected function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->storage = $this->getMock('Magento\Theme\Model\Wysiwyg\Storage', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->request = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            false,
            true,
            ['isPost', 'getParam']
        );

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $helper->getObject(
            'Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\DeleteFiles',
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'response' => $this->response,
            ]
        );
    }

    public function testExecuteWithWrongRequest()
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $jsonData = $this->getMock('Magento\Framework\Json\Helper\Data', [], [], '', false);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'Wrong request'])
            ->willReturn('{"error":"true","message":"Wrong request"}');

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Json\Helper\Data')
            ->willReturn($jsonData);

        $this->response->expects($this->once())
            ->method('representJson')
            ->with('{"error":"true","message":"Wrong request"}');

        $this->controller->execute();
    }

    public function testExecute()
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('files')
            ->willReturn('{"files":"file"}');

        $jsonData = $this->getMock('Magento\Framework\Json\Helper\Data', [], [], '', false);
        $jsonData->expects($this->once())
            ->method('jsonDecode')
            ->with('{"files":"file"}')
            ->willReturn(['files' => 'file']);
        $this->objectManager->expects($this->at(0))
            ->method('get')
            ->with('Magento\Framework\Json\Helper\Data')
            ->willReturn($jsonData);
        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with('Magento\Theme\Model\Wysiwyg\Storage')
            ->willReturn($this->storage);
        $this->storage->expects($this->once())
            ->method('deleteFile')
            ->with('file');

        $this->controller->execute();
    }
}
