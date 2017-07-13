<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTest
{
    /** @var \Magento\Catalog\Controller\Adminhtml\Product\Save */
    protected $action;

    /** @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject */
    private $resultPage;

    /** @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject */
    private $resultForward;

    /** @var \Magento\Catalog\Controller\Adminhtml\Product\Builder|\PHPUnit_Framework_MockObject_MockObject */
    private $productBuilder;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    private $product;

    /** @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $resultRedirectFactory;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    private $resultRedirect;

    /** @var Helper|\PHPUnit_Framework_MockObject_MockObject */
    private $initializationHelper;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $messageManagerMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->productBuilder = $this->createPartialMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Builder::class,
            ['build']
        );
        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)->disableOriginalConstructor()
            ->setMethods(['addData', 'getSku', 'getTypeId', 'getStoreId', '__sleep', '__wakeup'])->getMock();
        $this->product->expects($this->any())->method('getTypeId')->will($this->returnValue('simple'));
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue('1'));
        $this->productBuilder->expects($this->any())->method('build')->will($this->returnValue($this->product));

        $this->messageManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class
        );

        $this->resultPage = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultPageFactory->expects($this->any())->method('create')->willReturn($this->resultPage);

        $this->resultForward = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultForwardFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultForwardFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultForward);
        $this->resultPage->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $this->resultRedirectFactory = $this->createPartialMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create']
        );
        $this->resultRedirect = $this->createMock(\Magento\Backend\Model\View\Result\Redirect::class);
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->initializationHelper = $this->createMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::class
        );

        $additionalParams = ['resultRedirectFactory' => $this->resultRedirectFactory];

        $storeManagerInterfaceMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getCode']
        );

        $storeManagerInterfaceMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnSelf());

        $this->action = (new ObjectManagerHelper($this))->getObject(
            \Magento\Catalog\Controller\Adminhtml\Product\Save::class,
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
     * @param string $exceptionType
     * @param string $methodExpected
     * @return void
     * @dataProvider exceptionTypeDataProvider
     */
    public function testExecuteSetsProductDataToSessionAndRedirectsToNewActionOnError($exceptionType, $methodExpected)
    {
        $productData = ['product' => ['name' => 'test-name']];

        $this->request->expects($this->any())->method('getPostValue')->willReturn($productData);
        $this->initializationHelper->expects($this->any())->method('initialize')
            ->willReturn($this->product);
        $this->product->expects($this->any())->method('getSku')->willThrowException(new $exceptionType(__('message')));

        $this->resultRedirect->expects($this->once())->method('setPath')->with('catalog/*/new');

        $this->messageManagerMock->expects($this->once())
            ->method($methodExpected);

        $this->action->execute();
    }

    /**
     * @return array
     */
    public function exceptionTypeDataProvider()
    {
        return [
            [\Magento\Framework\Exception\LocalizedException::class, 'addExceptionMessage'],
            ['Exception', 'addErrorMessage']
        ];
    }
}
