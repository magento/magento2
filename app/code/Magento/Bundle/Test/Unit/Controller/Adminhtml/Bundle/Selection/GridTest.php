<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Controller\Adminhtml\Bundle\Selection;

use Magento\Backend\App\Action\Context;
use Magento\Bundle\Controller\Adminhtml\Bundle\Selection\Grid;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase
{
    /** @var Grid */
    protected $controller;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var MockObject|RequestInterface
     */
    protected $request;

    /**
     * @var MockObject|ResponseInterface
     */
    protected $response;

    /**
     * @var MockObject|ViewInterface
     */
    protected $view;

    /**
     * @var MockObject|Context
     */
    protected $context;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setBody'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();
        $this->view = $this->getMockForAbstractClass(ViewInterface::class);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->context->expects($this->any())
            ->method('getView')
            ->willReturn($this->view);

        $this->controller = $this->objectManagerHelper->getObject(
            Grid::class,
            [
                'context' => $this->context
            ]
        );
    }

    public function testExecute()
    {
        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $block = $this->getMockBuilder(
            \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid::class
        )
            ->disableOriginalConstructor()
            ->addMethods(['setIndex'])
            ->onlyMethods(['toHtml'])
            ->getMock();

        $this->response->expects($this->once())->method('setBody')->willReturnSelf();
        $this->request->expects($this->once())->method('getParam')->with('index')->willReturn('index');
        $this->view->expects($this->once())->method('getLayout')->willReturn($layout);
        $layout->expects($this->once())->method('createBlock')->willReturn($block);
        $block->expects($this->once())->method('setIndex')->willReturnSelf();
        $block->expects($this->once())->method('toHtml')->willReturnSelf();

        $this->assertEquals($this->response, $this->controller->execute());
    }

    public function testExecuteWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter "index"');

        $this->request->expects($this->once())->method('getParam')->with('index')->willReturn('<index"');

        $this->controller->execute();
    }
}
