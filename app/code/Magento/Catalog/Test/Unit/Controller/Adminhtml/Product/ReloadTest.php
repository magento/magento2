<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Reload;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Ui\Component\Wrapper\UiComponent;

/**
 * Class ReloadTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Reload
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productBuilderMock;

    /**
     * @var ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var UiComponent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentMock;

    /**
     * @var ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->productBuilderMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['forward', 'setJsonData', 'getLayout'])
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();
        $this->uiComponentMock = $this->getMockBuilder(UiComponent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorMock = $this->getMockBuilder(ProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultMock);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->productBuilderMock->expects($this->any())
            ->method('build')
            ->willReturn($this->productMock);
        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->willReturn($this->uiComponentMock);
        $this->layoutMock->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->processorMock);
        $this->resultMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);

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

        $this->assertSame(true, $this->model->execute());
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn('true');

        $this->assertInstanceOf(ResultInterface::class, $this->model->execute());
    }
}
