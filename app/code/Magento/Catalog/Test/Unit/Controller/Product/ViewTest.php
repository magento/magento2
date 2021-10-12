<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Product;

use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Product\View;
use Magento\Catalog\Helper\Product\View as ViewHelper;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManager\ObjectManager;
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
     * @var \Magento\Catalog\Helper\Product|MockObject
     */
    protected $helperProduct;

    /**
     * @var Magento\Framework\Controller\Result\Redirect|MockObject
     */
    protected $redirectMock;

    /**
     * @var Magento\Framework\UrlInterface|MockObject
     */
    protected $urlBuilder;

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
            ->setMethods(['isAjax', 'isPost', 'getParam'])
            ->getMockForAbstractClass();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $this->helperProduct = $this->createMock(\Magento\Catalog\Helper\Product::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Catalog\Helper\Product::class)
            ->willReturn($this->helperProduct);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $resultRedirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->redirectMock = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $resultRedirectFactoryMock->method('create')->willReturn($this->redirectMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactoryMock);
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $contextMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->urlBuilder);
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

    public function testExecuteRecentlyViewed(): void
    {
        $post = [
            'category' => '1',
            'id' => 1,
            'options' => false,
            View::PARAM_NAME_URL_ENCODED => 'some_param_url_encoded'
        ];

        // _initProduct
        $this->helperProduct->method('initProduct')
            ->willReturn('true');
        $this->redirectMock->method('setUrl')->with('productUrl')->willReturnSelf();

        $this->requestMock->method('isPost')
            ->willReturn(true);
        $this->requestMock->method('getParam')->willReturnCallback(
            function ($key) use ($post) {
                return $post[$key];
            }
        );

        $this->urlBuilder->expects($this->any())->method('getCurrentUrl')->willReturn('productUrl');
        $this->view->execute();
    }
}
