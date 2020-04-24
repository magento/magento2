<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Widget;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser;
use Magento\Catalog\Controller\Adminhtml\Category\Widget;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\View;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChooserTest extends TestCase
{
    /**
     * @var Widget
     */
    protected $controller;

    /**
     * @var Http|MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\Request\Http|MockObject
     */
    protected $requestMock;

    /**
     * @var View|MockObject
     */
    protected $viewMock;

    /**
     * @var Chooser|MockObject
     */
    protected $chooserBlockMock;

    /**
     * @var Layout|MockObject
     */
    protected $layoutMock;

    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Raw|MockObject
     */
    protected $resultRaw;

    protected function setUp(): void
    {
        $this->responseMock = $this->createMock(Http::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->viewMock = $this->createPartialMock(View::class, ['getLayout']);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $helper = new ObjectManager($this);

        $context = $this->getMockBuilder(Context::class)
            ->setMethods(['getRequest', 'getResponse', 'getMessageManager', 'getSession'])
            ->setConstructorArgs(
                $helper->getConstructArguments(
                    Context::class,
                    [
                        'response' => $this->responseMock,
                        'request' => $this->requestMock,
                        'view' => $this->viewMock,
                        'objectManager' => $this->objectManagerMock
                    ]
                )
            )
            ->getMock();

        $this->resultRaw = $this->getMockBuilder(Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRawFactory = $this->getMockBuilder(RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRawFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRaw);

        $this->layoutMock = $this->createPartialMock(Layout::class, ['createBlock']);
        $layoutFactory = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $layoutFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->layoutMock);

        $context->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $context->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->controller = new \Magento\Catalog\Controller\Adminhtml\Category\Widget\Chooser(
            $context,
            $layoutFactory,
            $resultRawFactory
        );
    }

    protected function _getTreeBlock()
    {
        $this->chooserBlockMock = $this->createMock(Chooser::class);

        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn(
            $this->chooserBlockMock
        );
    }

    public function testExecute()
    {
        $this->_getTreeBlock();
        $testHtml = '<div>Some test html</div>';
        $this->chooserBlockMock->expects($this->once())->method('toHtml')->willReturn($testHtml);
        $this->resultRaw->expects($this->once())->method('setContents')->with($testHtml);
        $this->controller->execute();
    }
}
