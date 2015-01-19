<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

class NewActionTest extends \Magento\Catalog\Controller\Adminhtml\ProductTest
{
    /** @var \Magento\Catalog\Controller\Adminhtml\Product\NewAction */
    protected $action;
    /** @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultPage;
    /** @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultForward;
    /** @var \Magento\Catalog\Controller\Adminhtml\Product\Builder|\PHPUnit_Framework_MockObject_MockObject */
    protected $productBuilder;
    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

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
            ->setMethods(['addData', 'getTypeId', 'getStoreId', '__sleep', '__wakeup'])->getMock();
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
        $resultPageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultPage);

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

        $this->action = new \Magento\Catalog\Controller\Adminhtml\Product\NewAction(
            $this->initContext(),
            $this->productBuilder,
            $this->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter')
                ->disableOriginalConstructor()->getMock(),
            $resultPageFactory,
            $resultForwardFactory
        );

        $this->resultPage->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($this->layout);
    }

    public function testExecute()
    {
        $this->action->getRequest()->expects($this->any())->method('getParam')->willReturn(true);
        $this->action->getRequest()->expects($this->any())->method('getFullActionName')
            ->willReturn('catalog_product_new');
        $this->action->execute();
    }

    public function testExecuteObtainsProductDataFromSession()
    {
        $this->action->getRequest()->expects($this->any())->method('getParam')->willReturn(true);
        $this->action->getRequest()->expects($this->any())->method('getFullActionName')
            ->willReturn('catalog_product_new');

        $this->session->expects($this->any())->method('getProductData')
            ->willReturn(['product' => ['name' => 'test-name']]);

        $this->product->expects($this->once())->method('addData')->with(['name' => 'test-name', 'stock_data' => null]);

        $this->action->execute();
    }
}
