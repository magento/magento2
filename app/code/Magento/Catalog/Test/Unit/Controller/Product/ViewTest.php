<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Product;

use Magento\Catalog\Helper\Product;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\UrlInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Product\View;
use Magento\Catalog\Helper\Product\View as ViewHelper;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Responsible for testing product view action on a strorefront.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var Product|MockObject
     */
    protected $helperProduct;

    /**
     * @var Redirect|MockObject
     */
    protected $redirectMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var Data|MockObject
     */
    protected $jsonHelperMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createPartialMock(
            Http::class,
            ['getParam', 'isPost']
        );
        $contextMock->method('getRequest')->willReturn($this->requestMock);
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $this->helperProduct = $this->createMock(Product::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->with(Product::class)
            ->willReturn($this->helperProduct);
        $contextMock->method('getObjectManager')->willReturn($objectManagerMock);
        $resultRedirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->redirectMock = $this->createMock(Redirect::class);
        $resultRedirectFactoryMock->method('create')->willReturn($this->redirectMock);
        $contextMock->method('getResultRedirectFactory')->willReturn($resultRedirectFactoryMock);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->urlBuilder->method('getUrl')->willReturn('productUrl');
        $contextMock->method('getUrl')->willReturn($this->urlBuilder);
        $viewHelperMock = $this->createMock(ViewHelper::class);
        $resultForwardFactoryMock = $this->createMock(ForwardFactory::class);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->catalogDesignMock = $this->createMock(Design::class);
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->productInterfaceMock = $this->createMock(ProductInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);

        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->jsonHelperMock = $this->createMock(Data::class);

        $this->view = new View(
            $contextMock,
            $viewHelperMock,
            $resultForwardFactoryMock,
            $this->resultPageFactoryMock,
            $this->loggerMock,
            $this->jsonHelperMock,
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
        $this->requestMock->method('isPost')->willReturn(false);
        $this->productRepositoryMock->method('getById')
            ->willReturn($this->productInterfaceMock);
        $dataObjectMock = $this->createPartialMock(DataObject::class, []);
        $dataObjectMock->setData('custom_design', 3);
        $this->catalogDesignMock->method('getDesignSettings')
            ->willReturn($dataObjectMock);
        $this->catalogDesignMock->expects($this->once())
            ->method('applyCustomDesign')
            ->with($themeId);
        $viewResultPageMock = $this->createMock(Page::class);
        $this->resultPageFactoryMock->method('create')
            ->willReturn($viewResultPageMock);
        $this->view->execute();
    }

    public function testExecuteRecentlyViewed(): void
    {
        $this->requestMock->method('getParam')->willReturnMap([
            ['category', null, '1'],
            ['id', null, 1],
            ['options', null, false],
            [View::PARAM_NAME_URL_ENCODED, null, 'some_param_url_encoded'],
        ]);

        // _initProduct
        $this->helperProduct->method('initProduct')
            ->willReturn($this->productInterfaceMock);
        $this->productRepositoryMock->method('getById')
            ->willReturn($this->productInterfaceMock);
        $dataObjectMock = $this->createPartialMock(DataObject::class, []);
        $dataObjectMock->setData('custom_design', null);
        $this->catalogDesignMock->method('getDesignSettings')->willReturn($dataObjectMock);
        $pageMock = $this->createMock(Page::class);
        $this->resultPageFactoryMock->method('create')->willReturn($pageMock);
        $this->redirectMock->method('setUrl')->with('productUrl')->willReturnSelf();

        $this->urlBuilder->method('getCurrentUrl')->willReturn('productUrl');
        $this->view->execute();
    }
}
