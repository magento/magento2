<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

class NewActionTest extends \Magento\Catalog\Controller\Adminhtml\ProductTest
{
    protected $action;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPage;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForward;

    protected function setUp()
    {
        $productBuilder = $this->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Builder')->setMethods([
                'build',
            ])->disableOriginalConstructor()->getMock();

        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getStoreId', '__sleep', '__wakeup'])->getMock();
        $product->expects($this->any())->method('getTypeId')->will($this->returnValue('simple'));
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue('1'));
        $productBuilder->expects($this->any())->method('build')->will($this->returnValue($product));

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
            $productBuilder,
            $this->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter')
                ->disableOriginalConstructor()->getMock(),
            $resultPageFactory,
            $resultForwardFactory
        );

        $this->resultPage->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($this->layout);
    }

    /**
     * Testing `newAction` method
     */
    public function testExecute()
    {
        $this->action->getRequest()->expects($this->at(0))->method('getParam')
            ->with('set')->will($this->returnValue(true));
        $this->action->getRequest()->expects($this->at(1))->method('getParam')
            ->with('popup')->will($this->returnValue(true));
        $this->action->getRequest()->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue('catalog_product_new'));
        $this->action->execute();
    }
}
