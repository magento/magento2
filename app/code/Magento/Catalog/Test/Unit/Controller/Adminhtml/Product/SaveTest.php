<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SaveTest extends \Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTest
{
    /** @var \Magento\Catalog\Controller\Adminhtml\Product\Save */
    protected $action;

    /** @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultPage;

    /** @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultForward;

    /** @var \Magento\Catalog\Controller\Adminhtml\Product\Builder|\PHPUnit_Framework_MockObject_MockObject */
    protected $productBuilder;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    /** @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirectFactory;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirect;

    /** @var Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $initializationHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->productBuilder = $this->getMock(
            'Magento\Catalog\Controller\Adminhtml\Product\Builder',
            ['build'],
            [],
            '',
            false
        );
        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->setMethods(['addData', 'getSku', 'getTypeId', 'getStoreId', '__sleep', '__wakeup'])->getMock();
        $this->product->expects($this->any())->method('getTypeId')->will($this->returnValue('simple'));
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue('1'));
        $this->productBuilder->expects($this->any())->method('build')->will($this->returnValue($this->product));

        $this->resultPage = $this->getMockBuilder('Magento\Backend\Model\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultPageFactory->expects($this->any())->method('create')->willReturn($this->resultPage);

        $this->resultForward = $this->getMockBuilder('Magento\Backend\Model\View\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();
        $resultForwardFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\ForwardFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultForwardFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultForward);
        $this->resultPage->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $this->resultRedirectFactory = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->resultRedirect = $this->getMock(
            'Magento\Backend\Model\View\Result\Redirect',
            [],
            [],
            '',
            false
        );
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->initializationHelper = $this->getMock(
            'Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper',
            [],
            [],
            '',
            false
        );

        $additionalParams = ['resultRedirectFactory' => $this->resultRedirectFactory];

        $storeManagerInterfaceMock = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
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
            'Magento\Catalog\Controller\Adminhtml\Product\Save',
            [
                'context' => $this->initContext($additionalParams),
                'productBuilder' => $this->productBuilder,
                'resultPageFactory' => $resultPageFactory,
                'resultForwardFactory' => $resultForwardFactory,
                'initializationHelper' => $this->initializationHelper,
                'storeManager' => $storeManagerInterfaceMock,
            ]
        );
    }

    /**
     * @param string $exceptionType
     * @return void
     * @dataProvider exceptionTypeDataProvider
     */
    public function testExecuteSetsProductDataToSessionAndRedirectsToNewActionOnError($exceptionType)
    {
        $productData = ['product' => ['name' => 'test-name']];

        $this->request->expects($this->any())->method('getPostValue')->willReturn($productData);
        $this->initializationHelper->expects($this->any())->method('initialize')
            ->willReturn($this->product);
        $this->product->expects($this->any())->method('getSku')->willThrowException(new $exceptionType(__('message')));

        $this->resultRedirect->expects($this->once())->method('setPath')->with('catalog/*/new');

        $this->action->execute();
    }

    /**
     * @return array
     */
    public function exceptionTypeDataProvider()
    {
        return [['Magento\Framework\Exception\LocalizedException'], ['Exception']];
    }
}
