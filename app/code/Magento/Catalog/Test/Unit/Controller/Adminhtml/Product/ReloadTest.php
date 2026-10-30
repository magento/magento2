<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\Reload;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Ui\Component\Wrapper\UiComponent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReloadTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Reload
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Builder|MockObject
     */
    protected $productBuilderMock;

    /**
     * @var ResultInterface|MockObject
     */
    protected $resultMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var UiComponent|MockObject
     */
    protected $uiComponentMock;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $processorMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->createMock(Context::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->productBuilderMock = $this->createMock(Builder::class);
        $this->resultMock = $this->createPartialMockWithReflection(
            ResultInterface::class,
            ['forward', 'setLayout', 'getLayout', 'setHttpResponseCode', 'setHeader', 'renderResult']
        );
        $layout = null;
        $this->resultMock->method('setLayout')->willReturnCallback(function ($value) use (&$layout) {
            $layout = $value;
            return $this->resultMock;
        });
        $this->resultMock->method('getLayout')->willReturnCallback(function () use (&$layout) {
            return $layout;
        });
        $this->resultMock->method('setHttpResponseCode')->willReturnSelf();
        $this->resultMock->method('setHeader')->willReturnSelf();
        $this->resultMock->method('renderResult')->willReturnSelf();
        $this->productMock = $this->createMock(ProductInterface::class);
        $this->uiComponentMock = $this->createMock(UiComponent::class);
        $this->processorMock = $this->createMock(ProcessorInterface::class);

        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->resultFactoryMock->method('create')->willReturn($this->resultMock);
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactoryMock);
        $this->productBuilderMock->method('build')->willReturn($this->productMock);
        $this->layoutMock->method('getBlock')->willReturn($this->uiComponentMock);
        $this->layoutMock->method('getUpdate')->willReturn($this->processorMock);
        $this->resultMock->setLayout($this->layoutMock);

        $this->model = $this->objectManager->getObject(Reload::class, [
            'context' => $this->contextMock,
            'productBuilder' => $this->productBuilderMock,
            'layout' => $this->layoutMock,
        ]);
    }

    public function testExecuteToBeRedirect()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(false);
        $this->resultMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturn(true);

        $this->assertTrue($this->model->execute());
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn('true');

        $this->assertInstanceOf(ResultInterface::class, $this->model->execute());
    }
}
