<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
    use MockCreationTrait;

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

        $this->context = $this->createMock(Context::class);
        $this->request = $this->createMock(RequestInterface::class);

        /** @var ResponseInterface $response */
        $this->response = $this->createPartialMockWithReflection(
            HttpResponse::class,
            ['setBody', 'getBody']
        );

        $this->productBuilder = $this->createPartialMock(Builder::class, ['build']);
        $this->initializationHelper = $this->createPartialMock(
            Helper::class,
            ['initialize']
        );
        $this->view = $this->createMock(ViewInterface::class);

        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getResponse')->willReturn($this->response);
        $this->context->method('getView')->willReturn($this->view);

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
        /** @var Product $product */
        $product = $this->createPartialMockWithReflection(Product::class, []);

        $layout = $this->createMock(LayoutInterface::class);

        /** @var AbstractBlock $block */
        $block = $this->createPartialMockWithReflection(
            AbstractBlock::class,
            ['toHtml', 'setHtmlResult']
        );
        $block->method('toHtml')->willReturn('');

        $this->productBuilder->expects($this->once())->method('build')->with($this->request)->willReturn($product);
        $this->initializationHelper->method('initialize')->willReturn($product);
        $this->response->method('getBody')->willReturn('');
        $this->view->expects($this->once())->method('getLayout')->willReturn($layout);
        $layout->expects($this->once())->method('createBlock')->willReturn($block);

        $this->controller->execute();
    }
}
