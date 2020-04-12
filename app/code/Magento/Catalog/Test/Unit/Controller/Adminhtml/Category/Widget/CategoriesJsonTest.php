<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Widget;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser;
use Magento\Catalog\Controller\Adminhtml\Category\Widget;
use Magento\Catalog\Controller\Adminhtml\Category\Widget\CategoriesJson;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\View;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoriesJsonTest extends TestCase
{
    /**
     * @var Widget
     */
    protected $controller;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

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
     * @var Json|MockObject
     */
    protected $resultJson;

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

        $this->resultJson = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultJsonFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->layoutMock = $this->createPartialMock(Layout::class, ['createBlock']);

        $layoutFactory = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $layoutFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->layoutMock);

        $context->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($this->responseMock));
        $this->registryMock = $this->createMock(Registry::class);
        $this->controller = new CategoriesJson(
            $context,
            $layoutFactory,
            $resultJsonFactory,
            $this->registryMock
        );
    }

    protected function _getTreeBlock()
    {
        $this->chooserBlockMock = $this->createMock(Chooser::class);
        $this->layoutMock->expects($this->once())->method('createBlock')->will(
            $this->returnValue($this->chooserBlockMock)
        );
    }

    public function testExecute()
    {
        $this->_getTreeBlock();
        $testCategoryId = 1;

        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValue($testCategoryId));
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())->method('load')->will($this->returnValue($categoryMock));
        $categoryMock->expects($this->once())->method('getId')->will($this->returnValue($testCategoryId));
        $this->objectManagerMock->expects($this->once())->method('create')
            ->with($this->equalTo(Category::class))->will($this->returnValue($categoryMock));

        $this->chooserBlockMock->expects($this->once())->method('setSelectedCategories')->will(
            $this->returnValue($this->chooserBlockMock)
        );
        $testHtml = '<div>Some test html</div>';
        $this->chooserBlockMock->expects($this->once())->method('getTreeJson')->will($this->returnValue($testHtml));
        $this->resultJson->expects($this->once())->method('setJsonData')->with($testHtml)->willReturnSelf();
        $this->controller->execute();
    }
}
