<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AddAttributeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $resultFactory;

    /** @var \Magento\ConfigurableProduct\Controller\Adminhtml\Product\AddAttribute */
    protected $controller;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $productBuilder;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Backend\App\Action\Context
     */
    protected $context;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $this->response = $this->createPartialMock(
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
        $this->view = $this->createMock(\Magento\Framework\App\ViewInterface::class);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);
        $this->context->expects($this->any())
            ->method('getView')
            ->willReturn($this->view);

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
        $resultLayout = $this->createMock(\Magento\Framework\View\Result\Layout::class);
        $this->resultFactory->expects($this->once())->method('create')->with(ResultFactory::TYPE_LAYOUT)
            ->willReturn($resultLayout);

        $this->assertInstanceOf(\Magento\Framework\View\Result\Layout::class, $this->controller->execute());
    }
}
