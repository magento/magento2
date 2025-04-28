<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTestCase;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\PageFactory;
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
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addData', 'getSku', 'getTypeId', 'getStoreId', '__sleep'])->getMock();
        $this->product->expects($this->any())->method('getTypeId')->willReturn('simple');
        $this->product->expects($this->any())->method('getStoreId')->willReturn('1');
        $this->productBuilder->expects($this->any())->method('build')->willReturn($this->product);

        $this->messageManagerMock = $this->getMockForAbstractClass(
            ManagerInterface::class
        );

        $this->resultPage = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resultPageFactory->expects($this->any())->method('create')->willReturn($this->resultPage);

        $this->resultForward = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultForwardFactory = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resultForwardFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultForward);
        $this->resultPage->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $this->resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->resultRedirect = $this->createMock(Redirect::class);
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->initializationHelper = $this->createMock(
            Helper::class
        );

        $additionalParams = ['resultRedirectFactory' => $this->resultRedirectFactory];

        $storeManagerInterfaceMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getCode']
        );

        $storeManagerInterfaceMock->expects($this->any())
            ->method('getStore')->willReturnSelf();

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
     * @dataProvider exceptionTypeDataProvider
     */
    public function testExecuteSetsProductDataToSessionAndRedirectsToNewActionOnError($exception, $methodExpected)
    {
        $productData = ['product' => ['name' => 'test-name']];

        $this->request->expects($this->any())->method('getPostValue')->willReturn($productData);
        $this->initializationHelper->expects($this->any())->method('initialize')
            ->willReturn($this->product);
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
