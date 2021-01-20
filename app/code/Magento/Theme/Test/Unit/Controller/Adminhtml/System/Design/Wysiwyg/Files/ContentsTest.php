<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Wysiwyg\Files;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContentsTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files */
    protected $controller;

    /** @var \Magento\Framework\App\ViewInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $view;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject*/
    protected $objectManager;

    /** @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $session;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject */
    protected $response;

    /** @var \Magento\Theme\Helper\Storage|\PHPUnit\Framework\MockObject\MockObject */
    protected $storage;

    protected function setUp(): void
    {
        $this->view = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->session = $this->createMock(\Magento\Backend\Model\Session::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->storage = $this->createMock(\Magento\Theme\Helper\Storage::class);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $helper->getObject(
            \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\Contents::class,
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
        $layout = $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class, [], '', false);
        $storage = $this->createMock(\Magento\Theme\Model\Wysiwyg\Storage::class);
        $block = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\BlockInterface::class,
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
            ->with(\Magento\Theme\Model\Wysiwyg\Storage::class)
            ->willReturn($storage);
        $this->storage->expects($this->once())
            ->method('getCurrentPath')
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

        $this->response->expects($this->once())
            ->method('representJson');

        $this->controller->execute();
    }
}
