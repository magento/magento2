<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\DeleteFiles;
use Magento\Theme\Helper\Storage;
use Magento\Theme\Model\Wysiwyg\Storage as WisiwygStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteFilesTest extends TestCase
{
    /**
     * @var Files
     */
    protected $controller;

    /**
     * @var MockObject|MockObject
     */
    protected $objectManager;

    /**
     * @var Storage|MockObject
     */
    protected $storage;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var Http|MockObject
     */
    protected $response;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->storage = $this->createMock(WisiwygStorage::class);
        $this->response = $this->createMock(Http::class);
        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['isPost', 'getParam']
        );

        $helper = new ObjectManager($this);
        $this->controller = $helper->getObject(
            DeleteFiles::class,
            [
                'objectManager' => $this->objectManager,
                'request' => $this->request,
                'response' => $this->response
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithWrongRequest(): void
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $jsonData = $this->createMock(Data::class);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'Wrong request'])
            ->willReturn('{"error":"true","message":"Wrong request"}');

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->willReturn($jsonData);

        $this->response->expects($this->once())
            ->method('representJson')
            ->with('{"error":"true","message":"Wrong request"}');

        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('files')
            ->willReturn('{"files":"file"}');

        $jsonData = $this->createMock(Data::class);
        $jsonData->expects($this->once())
            ->method('jsonDecode')
            ->with('{"files":"file"}')
            ->willReturn(['files' => 'file']);
        $this->objectManager
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [Data::class] => $jsonData,
                [WisiwygStorage::class] => $this->storage
            });
        $this->storage->expects($this->once())
            ->method('deleteFile')
            ->with('file');

        $this->controller->execute();
    }
}
