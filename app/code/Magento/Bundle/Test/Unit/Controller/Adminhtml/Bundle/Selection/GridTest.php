<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Controller\Adminhtml\Bundle\Selection;

use Magento\Backend\App\Action\Context;
use Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid as SearchGrid;
use Magento\Bundle\Controller\Adminhtml\Bundle\Selection\Grid;
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

class GridTest extends TestCase
{
    use MockCreationTrait;

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

        $this->context = $this->createMock(Context::class);
        $this->request = $this->createMock(RequestInterface::class);

        /** @var ResponseInterface $response */
        $this->response = $this->createPartialMockWithReflection(
            HttpResponse::class,
            ['setBody', 'getBody']
        );
        $this->response->method('setBody')->willReturnSelf();

        $this->view = $this->createMock(ViewInterface::class);

        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getResponse')->willReturn($this->response);
        $this->context->method('getView')->willReturn($this->view);

        $this->controller = $this->objectManagerHelper->getObject(
            Grid::class,
            [
                'context' => $this->context
            ]
        );
    }

    public function testExecute()
    {
        $layout = $this->createMock(LayoutInterface::class);

        /** @var AbstractBlock $block */
        $block = $this->createPartialMockWithReflection(
            AbstractBlock::class,
            ['setIndex', 'toHtml']
        );
        $block->method('toHtml')->willReturn('');
        $block->method('setIndex')->willReturnSelf();

        $this->response->method('getBody')->willReturn('');
        $this->request->expects($this->once())->method('getParam')->with('index')->willReturn('index');
        $this->view->expects($this->once())->method('getLayout')->willReturn($layout);
        $layout->expects($this->once())->method('createBlock')->willReturn($block);
        $block->setIndex('index');

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
