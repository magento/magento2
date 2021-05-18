<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Product\View;
use Magento\Catalog\Helper\Product\View as ViewHelper;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Responsible for testing product view action on a strorefront.
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    private $view;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var Design|MockObject
     */
    private $catalogDesignMock;

    /**
     * @var ProductRepository|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $productInterfaceMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['isPost'])
            ->getMockForAbstractClass();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $viewHelperMock = $this->getMockBuilder(ViewHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogDesignMock = $this->getMockBuilder(Design::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productInterfaceMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);

        $this->view = new View(
            $contextMock,
            $viewHelperMock,
            $resultForwardFactoryMock,
            $this->resultPageFactoryMock,
            null,
            null,
            $this->catalogDesignMock,
            $this->productRepositoryMock,
            $this->storeManagerMock
        );
    }

    /**
     * Verify that product custom design theme is applied before product rendering
     */
    public function testExecute(): void
    {
        $themeId = 3;
        $this->requestMock->method('isPost')
            ->willReturn(false);
        $this->productRepositoryMock->method('getById')
            ->willReturn($this->productInterfaceMock);
        $dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomDesign'])
            ->getMock();
        $dataObjectMock->method('getCustomDesign')
            ->willReturn($themeId);
        $this->catalogDesignMock->method('getDesignSettings')
            ->willReturn($dataObjectMock);
        $this->catalogDesignMock->expects($this->once())
            ->method('applyCustomDesign')
            ->with($themeId);
        $viewResultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock->method('create')
            ->willReturn($viewResultPageMock);
        $this->view->execute();
    }
}
