<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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

        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getResponse', 'getMessageManager', 'getSession', 'getObjectManager']
        );

        $this->resultJson = $this->createMock(Json::class);
        $resultJsonFactory = $this->createPartialMock(JsonFactory::class, ['create']);
        $resultJsonFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->layoutMock = $this->createPartialMock(Layout::class, ['createBlock']);

        $layoutFactory = $this->createPartialMock(LayoutFactory::class, ['create']);
        $layoutFactory->method('create')->willReturn($this->layoutMock);

        $context->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $context->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $context->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
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
        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn(
            $this->chooserBlockMock
        );
    }

    public function testExecute()
    {
        $this->_getTreeBlock();
        $testCategoryId = 1;

        $this->requestMock->method('getPost')->willReturn($testCategoryId);
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())->method('load')->willReturn($categoryMock);
        $categoryMock->expects($this->once())->method('getId')->willReturn($testCategoryId);
        $this->objectManagerMock->expects($this->once())->method('create')
            ->with(Category::class)->willReturn($categoryMock);

        $this->chooserBlockMock->expects($this->once())->method('setSelectedCategories')->willReturn(
            $this->chooserBlockMock
        );
        $testHtml = '<div>Some test html</div>';
        $this->chooserBlockMock->expects($this->once())->method('getTreeJson')->willReturn($testHtml);
        $this->resultJson->expects($this->once())->method('setJsonData')->with($testHtml)->willReturnSelf();
        $this->controller->execute();
    }
}
