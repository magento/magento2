<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AddAttributeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\ConfigurableProduct\Controller\Adminhtml\Product\AddAttribute */
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $productBuilder;

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

        $this->context = $this->getMockBuilder('\Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->response = $this->getMock(
            '\Magento\Framework\App\ResponseInterface',
            [
                'sendResponse',
                'setBody'
            ]
        );
        $this->productBuilder = $this->getMockBuilder('\Magento\Catalog\Controller\Adminhtml\Product\Builder')
            ->disableOriginalConstructor()
            ->setMethods(['build'])
            ->getMock();
        $this->view = $this->getMock('\Magento\Framework\App\ViewInterface');

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
            '\Magento\ConfigurableProduct\Controller\Adminhtml\Product\AddAttribute',
            [
                'context' => $this->context,
                'productBuilder' => $this->productBuilder
            ]
        );
    }

    public function testExecute()
    {
        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['_wakeup', 'getId'])
            ->getMock();
        $layout = $this->getMock('\Magento\Framework\View\LayoutInterface');
        $block = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\NewAttribute\Product\Created'
        )
            ->disableOriginalConstructor()
            ->setMethods(['setIndex', 'toHtml'])
            ->getMock();

        $this->view->expects($this->once())->method('loadLayout')->with('popup')->willReturnSelf();
        $this->productBuilder->expects($this->once())->method('build')->with($this->request)->willReturn($product);
        $this->view->expects($this->any())->method('getLayout')->willReturn($layout);
        $layout->expects($this->once())->method('createBlock')->willReturn($block);
        $layout->expects($this->once())->method('setChild')->willReturnSelf();
        $this->view->expects($this->any())->method('renderLayout')->willReturnSelf();

        $this->controller->execute();
    }
}
