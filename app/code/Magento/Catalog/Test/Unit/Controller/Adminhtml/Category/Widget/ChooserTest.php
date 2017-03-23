<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Widget;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChooserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Category\Widget
     */
    protected $controller;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $chooserBlockMock;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRaw;

    protected function setUp()
    {
        $this->responseMock = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->viewMock = $this->getMock(\Magento\Framework\App\View::class, ['getLayout'], [], '', false);
        $this->objectManagerMock = $this->getMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            [],
            [],
            '',
            false
        );
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $context = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest', 'getResponse', 'getMessageManager', 'getSession'],
            $helper->getConstructArguments(
                \Magento\Backend\App\Action\Context::class,
                [
                    'response' => $this->responseMock,
                    'request' => $this->requestMock,
                    'view' => $this->viewMock,
                    'objectManager' => $this->objectManagerMock
                ]
            )
        );

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

        $this->layoutMock = $this->getMock(\Magento\Framework\View\Layout::class, ['createBlock'], [], '', false);
        $layoutFactory = $this->getMockBuilder(\Magento\Framework\View\LayoutFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $layoutFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->layoutMock);

        $context->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($this->responseMock));
        $this->controller = new \Magento\Catalog\Controller\Adminhtml\Category\Widget\Chooser(
            $context, $layoutFactory, $resultRawFactory
        );
    }

    protected function _getTreeBlock()
    {
        $this->chooserBlockMock = $this->getMock(
            \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser::class, [], [], '', false
        );

        $this->layoutMock->expects($this->once())->method('createBlock')->will(
            $this->returnValue($this->chooserBlockMock)
        );
    }

    public function testExecute()
    {
        $this->_getTreeBlock();
        $testHtml = '<div>Some test html</div>';
        $this->chooserBlockMock->expects($this->once())->method('toHtml')->will($this->returnValue($testHtml));
        $this->resultRaw->expects($this->once())->method('setContents')->with($testHtml);
        $this->controller->execute();
    }
}
