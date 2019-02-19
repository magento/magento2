<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Controller\Adminhtml\Bundle\Selection;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class GridTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Bundle\Controller\Adminhtml\Bundle\Selection\Grid */
    protected $controller;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\App\Action\Context
     */
    protected $context;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->response = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            [
                'sendResponse',
                'setBody'
            ]
        );
        $this->view = $this->createMock(\Magento\Framework\App\ViewInterface::class);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->context->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->response));
        $this->context->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->controller = $this->objectManagerHelper->getObject(
            \Magento\Bundle\Controller\Adminhtml\Bundle\Selection\Grid::class,
            [
                'context' => $this->context
            ]
        );
    }

    public function testExecute()
    {
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $block = $this->getMockBuilder(
            \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['setIndex', 'toHtml'])
            ->getMock();

        $this->response->expects($this->once())->method('setBody')->willReturnSelf();
        $this->request->expects($this->once())->method('getParam')->with('index')->willReturn('index');
        $this->view->expects($this->once())->method('getLayout')->willReturn($layout);
        $layout->expects($this->once())->method('createBlock')->willReturn($block);
        $block->expects($this->once())->method('setIndex')->willReturnSelf();
        $block->expects($this->once())->method('toHtml')->willReturnSelf();

        $this->assertEquals($this->response, $this->controller->execute());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid parameter "index"
     */
    public function testExecuteWithException()
    {
        $this->request->expects($this->once())->method('getParam')->with('index')->willReturn('<index"');

        $this->controller->execute();
    }
}
