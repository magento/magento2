<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;
use Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit\Form;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
    /** @var Form */
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
     * @var MockObject|Builder
     */
    protected $productBuilder;

    /**
     * @var MockObject|Helper
     */
    protected $initializationHelper;

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
        $this->productBuilder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['build'])
            ->getMock();
        $this->initializationHelper = $this->getMockBuilder(
            Helper::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['initialize'])
            ->getMock();
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
            Form::class,
            [
                'context' => $this->context,
                'productBuilder' => $this->productBuilder,
                'initializationHelper' => $this->initializationHelper
            ]
        );
    }

    public function testExecute()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['_wakeup'])
            ->onlyMethods(['getId'])
            ->getMock();
        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $block = $this->getMockBuilder(Bundle::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIndex'])
            ->onlyMethods(['toHtml'])
            ->getMock();

        $this->productBuilder->expects($this->once())->method('build')->with($this->request)->willReturn($product);
        $this->initializationHelper->expects($this->any())->method('initialize')->willReturn($product);
        $this->response->expects($this->once())->method('setBody')->willReturnSelf();
        $this->view->expects($this->once())->method('getLayout')->willReturn($layout);
        $layout->expects($this->once())->method('createBlock')->willReturn($block);
        $block->expects($this->once())->method('toHtml')->willReturnSelf();

        $this->controller->execute();
    }
}
