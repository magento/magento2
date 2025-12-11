<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Controller\Adminhtml\Product\AddAttribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\App\Test\Unit\Helper\ResponseTestHelper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddAttributeTest extends TestCase
{
    /** @var ResultFactory|MockObject */
    private $resultFactory;

    /** @var AddAttribute */
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

        $this->context = $this->createMock(Context::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->response = new ResponseTestHelper();
        $this->productBuilder = $this->createPartialMock(Builder::class, ['build']);
        $this->view = $this->createMock(ViewInterface::class);

        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getResponse')->willReturn($this->response);
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);
        $this->context->method('getView')->willReturn($this->view);

        $this->controller = $this->objectManagerHelper->getObject(
            AddAttribute::class,
            [
                'context' => $this->context,
                'productBuilder' => $this->productBuilder
            ]
        );
    }

    public function testExecute()
    {
        $product = $this->createPartialMock(Product::class, ['getId']);

        $this->productBuilder->expects($this->once())->method('build')->with($this->request)->willReturn($product);
        $resultLayout = $this->createMock(Layout::class);
        $this->resultFactory->expects($this->once())->method('create')->with(ResultFactory::TYPE_LAYOUT)
            ->willReturn($resultLayout);

        $this->assertInstanceOf(Layout::class, $this->controller->execute());
    }
}
