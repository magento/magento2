<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTestCase;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends ProductTestCase
{
    /** @var Save */
    protected $action;

    /** @var Page|MockObject */
    private $resultPage;

    /** @var Forward|MockObject */
    private $resultForward;

    /** @var Builder|MockObject */
    private $productBuilder;

    /** @var Product|MockObject */
    private $product;

    /** @var RedirectFactory|MockObject */
    private $resultRedirectFactory;

    /** @var Redirect|MockObject */
    private $resultRedirect;

    /** @var Helper|MockObject */
    private $initializationHelper;

    /** @var ManagerInterface|MockObject */
    private $messageManagerMock;

    /**
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
            ['addData', 'getSku', 'getTypeId', 'getStoreId', '__sleep']
        );
        $this->product->method('getTypeId')->willReturn('simple');
        $this->product->method('getStoreId')->willReturn('1');
        $this->productBuilder->method('build')->willReturn($this->product);

        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

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

        $additionalParams = ['resultRedirectFactory' => $this->resultRedirectFactory];

        $storeManagerInterfaceMock = $this->createMock(StoreManagerInterface::class);
        
        // Create a Store mock with getCode method
        $storeMock = $this->createPartialMock(Store::class, ['getCode']);
        $storeMock->method('getCode')->willReturn('default');
        
        $storeManagerInterfaceMock->method('getStore')->willReturn($storeMock);

        $this->action = (new ObjectManagerHelper($this))->getObject(
            Save::class,
            [
                'context' => $this->initContext($additionalParams),
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'productBuilder' => $this->productBuilder,
                'resultPageFactory' => $resultPageFactory,
                'resultForwardFactory' => $resultForwardFactory,
                'initializationHelper' => $this->initializationHelper,
                'storeManager' => $storeManagerInterfaceMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
    }

    /**
     * @param \Exception $exception
     * @param string $methodExpected
     * @return void
     */
    #[DataProvider('exceptionTypeDataProvider')]
    public function testExecuteSetsProductDataToSessionAndRedirectsToNewActionOnError($exception, $methodExpected)
    {
        $productData = ['product' => ['name' => 'test-name']];

        $this->request->method('getPostValue')->willReturn($productData);
        $this->initializationHelper->method('initialize')->willReturn($this->product);
        $this->product->expects($this->any())->method('getSku')->willThrowException($exception);

        $this->resultRedirect->expects($this->once())->method('setPath')->with('catalog/*/new');

        $this->messageManagerMock->expects($this->once())
            ->method($methodExpected);

        $this->action->execute();
    }

    /**
     * @return array
     */
    public static function exceptionTypeDataProvider()
    {
        return [
            [new LocalizedException(__('Message')), 'addExceptionMessage'],
            [new \Exception('Message'), 'addErrorMessage']
        ];
    }
}
