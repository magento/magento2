<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class DeleteFilesTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files */
    protected $controller;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject*/
    protected $objectManager;

    /** @var \Magento\Theme\Helper\Storage|\PHPUnit\Framework\MockObject\MockObject */
    protected $storage;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject */
    protected $response;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->storage = $this->createMock(\Magento\Theme\Model\Wysiwyg\Storage::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['isPost', 'getParam']
        );

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $helper->getObject(
            \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\DeleteFiles::class,
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

        $jsonData = $this->createMock(\Magento\Framework\Json\Helper\Data::class);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'Wrong request'])
            ->willReturn('{"error":"true","message":"Wrong request"}');

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
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

        $jsonData = $this->createMock(\Magento\Framework\Json\Helper\Data::class);
        $jsonData->expects($this->once())
            ->method('jsonDecode')
            ->with('{"files":"file"}')
            ->willReturn(['files' => 'file']);
        $this->objectManager->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->willReturn($jsonData);
        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Theme\Model\Wysiwyg\Storage::class)
            ->willReturn($this->storage);
        $this->storage->expects($this->once())
            ->method('deleteFile')
            ->with('file');

        $this->controller->execute();
    }
}
