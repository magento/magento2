<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Widget;

use Magento\Catalog\Controller\Adminhtml\Product\Widget\Chooser;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for Magento\Catalog\Controller\Adminhtml\Product\Widget\Chooser.
 */
class ChooserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Chooser
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rawFactoryMock;

    /**
     * @var LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestInterfaceMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->rawFactoryMock = $this->createMock(\Magento\Framework\Controller\Result\RawFactory::class);
        $this->layoutFactoryMock = $this->createMock(\Magento\Framework\View\LayoutFactory::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->requestInterfaceMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['isPost']
        );
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);

        $this->controller = $objectManagerHelper->getObject(
            \Magento\Catalog\Controller\Adminhtml\Product\Widget\Chooser::class,
            [
                'context' => $this->contextMock,
                'resultRawFactory' => $this->rawFactoryMock,
                'layoutFactory' => $this->layoutFactoryMock,
            ]
        );
    }

    /**
     * Check that error throws when request is not a POST.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Page not found.
     */
    public function testExecuteWithNonPostRequest()
    {
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(false);

        $this->controller->execute();
    }
}
