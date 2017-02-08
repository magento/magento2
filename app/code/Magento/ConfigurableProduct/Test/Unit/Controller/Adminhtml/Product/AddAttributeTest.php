<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AddAttributeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $resultFactory;

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

        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $this->resultFactory = $this->getMock(\Magento\Framework\Controller\ResultFactory::class, [], [], '', false);
        $this->response = $this->getMock(
            \Magento\Framework\App\ResponseInterface::class,
            [
                'sendResponse',
                'setBody'
            ]
        );
        $this->productBuilder = $this->getMockBuilder(\Magento\Catalog\Controller\Adminhtml\Product\Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['build'])
            ->getMock();
        $this->view = $this->getMock(\Magento\Framework\App\ViewInterface::class);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->context->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->response));
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->will($this->returnValue($this->resultFactory));
        $this->context->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->controller = $this->objectManagerHelper->getObject(
            \Magento\ConfigurableProduct\Controller\Adminhtml\Product\AddAttribute::class,
            [
                'context' => $this->context,
                'productBuilder' => $this->productBuilder
            ]
        );
    }

    public function testExecute()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['_wakeup', 'getId'])
            ->getMock();

        $this->productBuilder->expects($this->once())->method('build')->with($this->request)->willReturn($product);
        $resultLayout = $this->getMock(\Magento\Framework\View\Result\Layout::class, [], [], '', false);
        $this->resultFactory->expects($this->once())->method('create')->with(ResultFactory::TYPE_LAYOUT)
            ->willReturn($resultLayout);

        $this->assertInstanceOf(\Magento\Framework\View\Result\Layout::class, $this->controller->execute());
    }
}
