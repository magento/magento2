<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Exception;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\Contents;
use Magento\Theme\Helper\Storage;
use Magento\Theme\Model\Wysiwyg\Storage as WysiwygStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContentsTest extends TestCase
{
    /**
     * @var Files
     */
    protected $controller;

    /**
     * @var ViewInterface|MockObject
     */
    protected $view;

    /**
     * @var MockObject|MockObject
     */
    protected $objectManager;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var Http|MockObject
     */
    protected $response;

    /**
     * @var Storage|MockObject
     */
    protected $storage;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->view = $this->getMockForAbstractClass(ViewInterface::class);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->session = $this->createMock(Session::class);
        $this->response = $this->createMock(Http::class);
        $this->storage = $this->createMock(Storage::class);

        $helper = new ObjectManager($this);
        $this->controller = $helper->getObject(
            Contents::class,
            [
                'objectManager' => $this->objectManager,
                'view' => $this->view,
                'session' => $this->session,
                'response' => $this->response,
                'storage' => $this->storage
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $layout = $this->getMockForAbstractClass(LayoutInterface::class, [], '', false);
        $storage = $this->createMock(WysiwygStorage::class);
        $block = $this->getMockForAbstractClass(
            BlockInterface::class,
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
        $this->storage->expects($this->once())
            ->method('getCurrentPath')
            ->willThrowException(new Exception('Message'));

        $jsonData = $this->createMock(Data::class);
        $jsonData->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => 'Message'])
            ->willReturn('{"error":"true","message":"Message"}');

        $this->objectManager
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [WysiwygStorage::class] => $storage,
                [Data::class] => $jsonData
            });
        $this->response->expects($this->once())
            ->method('representJson');

        $this->controller->execute();
    }
}
