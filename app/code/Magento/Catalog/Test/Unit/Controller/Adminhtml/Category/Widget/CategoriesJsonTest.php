<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Widget;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoriesJsonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Category\Widget
     */
    protected $controller;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

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
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJson;

    protected function setUp()
    {
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->viewMock = $this->createPartialMock(\Magento\Framework\App\View::class, ['getLayout']);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->setMethods(['getRequest', 'getResponse', 'getMessageManager', 'getSession'])
            ->setConstructorArgs($helper->getConstructArguments(
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

        $this->resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultJsonFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['createBlock']);

        $layoutFactory = $this->getMockBuilder(\Magento\Framework\View\LayoutFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $layoutFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->layoutMock);

        $context->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($this->responseMock));
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->controller = new \Magento\Catalog\Controller\Adminhtml\Category\Widget\CategoriesJson(
            $context, $layoutFactory, $resultJsonFactory, $this->registryMock
        );
    }

    protected function _getTreeBlock()
    {
        $this->chooserBlockMock = $this->createMock(\Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser::class);
        $this->layoutMock->expects($this->once())->method('createBlock')->will(
            $this->returnValue($this->chooserBlockMock)
        );
    }

    public function testExecute()
    {
        $this->_getTreeBlock();
        $testCategoryId = 1;

        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValue($testCategoryId));
        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);
        $categoryMock->expects($this->once())->method('load')->will($this->returnValue($categoryMock));
        $categoryMock->expects($this->once())->method('getId')->will($this->returnValue($testCategoryId));
        $this->objectManagerMock->expects($this->once())->method('create')
            ->with($this->equalTo(\Magento\Catalog\Model\Category::class))->will($this->returnValue($categoryMock));

        $this->chooserBlockMock->expects($this->once())->method('setSelectedCategories')->will(
            $this->returnValue($this->chooserBlockMock)
        );
        $testHtml = '<div>Some test html</div>';
        $this->chooserBlockMock->expects($this->once())->method('getTreeJson')->will($this->returnValue($testHtml));
        $this->resultJson->expects($this->once())->method('setJsonData')->with($testHtml)->willReturnSelf();
        $this->controller->execute();
    }
}
