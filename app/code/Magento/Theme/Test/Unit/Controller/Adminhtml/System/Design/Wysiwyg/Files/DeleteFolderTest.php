<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Magento\Framework\App\Response\Http;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\DeleteFolder;
use Magento\Theme\Helper\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteFolderTest extends TestCase
{
    /** @var Files */
    protected $controller;

    /** @var MockObject|MockObject*/
    protected $objectManager;

    /** @var Http|MockObject */
    protected $response;

    /** @var Storage|MockObject */
    protected $storage;

    /** @var Storage|MockObject */
    protected $storageHelper;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->response = $this->createMock(Http::class);
        $this->storage = $this->createMock(\Magento\Theme\Model\Wysiwyg\Storage::class);
        $this->storageHelper = $this->createMock(Storage::class);

        $helper = new ObjectManager($this);
        $this->controller = $helper->getObject(
            DeleteFolder::class,
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

        $jsonData = $this->createMock(Data::class);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'Message'])
            ->willReturn('{"error":"true","message":"Message"}');

        $this->objectManager->expects($this->at(1))
            ->method('get')
            ->with(Data::class)
            ->willReturn($jsonData);

        $this->controller->execute();
    }
}
