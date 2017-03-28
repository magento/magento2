<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class DeleteFolderTest extends \PHPUnit_Framework_TestCase
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
        $this->objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->response = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->storage = $this->getMock(\Magento\Theme\Model\Wysiwyg\Storage::class, [], [], '', false);
        $this->storageHelper = $this->getMock(\Magento\Theme\Helper\Storage::class, [], [], '', false);

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

        $jsonData = $this->getMock(\Magento\Framework\Json\Helper\Data::class, [], [], '', false);
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
