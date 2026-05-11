<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Controller\Adminhtml\Product\Validate;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTestCase;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends ProductTestCase
{
    /** @var Validate */
    protected $action;

    /** @var Page|MockObject */
    protected $resultPage;

    /** @var Forward|MockObject */
    protected $resultForward;

    /** @var Builder|MockObject */
    protected $productBuilder;

    /** @var Product|MockObject */
    protected $product;

    /** @var RedirectFactory|MockObject */
    protected $resultRedirectFactory;

    /** @var Redirect|MockObject */
    protected $resultRedirect;

    /** @var Helper|MockObject */
    protected $initializationHelper;

    /** @var ProductFactory|MockObject */
    protected $productFactory;

    /** @var Json|MockObject */
    protected $resultJson;

    /** @var JsonFactory|MockObject */
    protected $resultJsonFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp(): void
    {
        $this->productBuilder = $this->createPartialMock(
            Builder::class,
            ['build']
        );
        $this->product = $this->createPartialMock(
            Product::class,
            ['addData', 'getSku', 'getTypeId', 'getStoreId', '__sleep', 'getAttributes', 'setAttributeSetId']
        );
        $this->product->method('getTypeId')->willReturn('simple');
        $this->product->method('getStoreId')->willReturn('1');
        $this->product->method('getAttributes')->willReturn([]);
        $this->productBuilder->method('build')->willReturn($this->product);

        $this->resultPage = $this->createMock(Page::class);

        $resultPageFactory = $this->createPartialMock(PageFactory::class, ['create']);
        $resultPageFactory->method('create')->willReturn($this->resultPage);

        $this->resultForward = $this->createMock(Forward::class);
        $resultForwardFactory = $this->createPartialMock(ForwardFactory::class, ['create']);
        $resultForwardFactory->method('create')->willReturn($this->resultForward);
        $this->resultPage->method('getLayout')->willReturn($this->layout);
        $this->resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->resultRedirect = $this->createMock(Redirect::class);
        $this->resultRedirectFactory->method('create')->willReturn($this->resultRedirect);

        $this->initializationHelper = $this->createMock(
            Helper::class
        );

        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->productFactory->method('create')->willReturn($this->product);

        $this->resultJson = $this->createMock(Json::class);
        $this->resultJsonFactory = $this->createPartialMock(JsonFactory::class, ['create']);
        $this->resultJsonFactory->method('create')->willReturn($this->resultJson);

        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getCode')->willReturn('default');
        
        $storeManagerInterfaceMock = $this->createMock(StoreManagerInterface::class);
        $storeManagerInterfaceMock->expects($this->any())
            ->method('getStore')->willReturn($storeMock);

        $additionalParams = ['resultRedirectFactory' => $this->resultRedirectFactory];
        $this->action = (new ObjectManagerHelper($this))->getObject(
            Validate::class,
            [
                'context' => $this->initContext($additionalParams),
                'productBuilder' => $this->productBuilder,
                'resultPageFactory' => $resultPageFactory,
                'resultForwardFactory' => $resultForwardFactory,
                'initializationHelper' => $this->initializationHelper,
                'resultJsonFactory' => $this->resultJsonFactory,
                'productFactory' => $this->productFactory,
                'storeManager' => $storeManagerInterfaceMock,
            ]
        );
    }

    public function _testAttributeSetIsObtainedFromPostByDefault()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap([['set', null, 4]]);
        $this->request->expects($this->any())->method('getPost')->willReturnMap([
            ['set', null, 9],
            ['product', [], []],
        ]);
        $this->product->expects($this->once())->method('setAttributeSetId')->with(9);
        $this->initializationHelper->method('initializeFromData')->willReturn($this->product);

        $this->action->execute();
    }

    public function _testAttributeSetIsObtainedFromGetWhenThereIsNoOneInPost()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap([['set', null, 4]]);
        $this->request->expects($this->any())->method('getPost')->willReturnMap([
            ['set', null, null],
            ['product', [], []],
        ]);
        $this->product->expects($this->once())->method('setAttributeSetId')->with(4);
        $this->initializationHelper->method('initializeFromData')->willReturn($this->product);

        $this->action->execute();
    }

    public function testInitializeFromData()
    {
        $productData = ['name' => 'test-name', 'stock_data' => ['use_config_manage_stock' => 0]];
        $this->request->expects($this->any())->method('getPost')->willReturnMap([
            ['product', [], $productData],
        ]);

        $this->initializationHelper
            ->expects($this->once())
            ->method('initializeFromData')
            ->with($this->product, $productData)
            ->willReturn($this->product);

        $this->action->execute();
    }
}
