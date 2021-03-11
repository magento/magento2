<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Widget;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChooserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Category\Widget
     */
    protected $controller;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $chooserBlockMock;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRaw;

    protected function setUp(): void
    {
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->viewMock = $this->createPartialMock(\Magento\Framework\App\View::class, ['getLayout']);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->setMethods(['getRequest', 'getResponse', 'getMessageManager', 'getSession'])
            ->setConstructorArgs(
                $helper->getConstructArguments(
                    \Magento\Backend\App\Action\Context::class,
                    [
                        'response' => $this->responseMock,
                        'request' => $this->requestMock,
                        'view' => $this->viewMock,
                        'objectManager' => $this->objectManagerMock
                    ]
                )
            )
            ->getMock();

        $this->resultRaw = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRawFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRawFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRaw);

        $this->layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['createBlock']);
        $layoutFactory = $this->getMockBuilder(\Magento\Framework\View\LayoutFactory::class)
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
        $this->chooserBlockMock = $this->createMock(\Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser::class);

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
