<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class DeleteFolderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files */
    protected $controller;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\PHPUnit_Framework_MockObject_MockObject*/
    protected $objectManager;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    /** @var \Magento\Theme\Helper\Storage|\PHPUnit_Framework_MockObject_MockObject */
    protected $storage;

    /** @var \Magento\Theme\Helper\Storage|\PHPUnit_Framework_MockObject_MockObject */
    protected $storageHelper;

    protected function setUp()
    {
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->storage = $this->createMock(\Magento\Theme\Model\Wysiwyg\Storage::class);
        $this->storageHelper = $this->createMock(\Magento\Theme\Helper\Storage::class);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $helper->getObject(
            \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\DeleteFolder::class,
            [
                'objectManager' => $this->objectManager,
                'response' => $this->response,
                'storage' => $this->storageHelper
            ]
        );
    }

    public function testExecute()
    {
        $this->storageHelper->expects($this->once())
            ->method('getCurrentPath')
            ->willReturn('/current/path/');

        $this->objectManager->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Theme\Model\Wysiwyg\Storage::class)
            ->willReturn($this->storage);
        $this->storage->expects($this->once())
            ->method('deleteDirectory')
            ->with('/current/path/')
            ->willThrowException(new \Exception('Message'));

        $jsonData = $this->createMock(\Magento\Framework\Json\Helper\Data::class);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'Message'])
            ->willReturn('{"error":"true","message":"Message"}');

        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->willReturn($jsonData);

        $this->controller->execute();
    }
}
