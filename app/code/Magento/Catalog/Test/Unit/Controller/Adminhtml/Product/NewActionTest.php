<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Controller\Adminhtml\Product\NewAction;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class NewActionTest extends \Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTest
{
    /** @var NewAction */
    protected $action;

    /** @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultPage;

    /** @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultForward;

    /** @var \Magento\Catalog\Controller\Adminhtml\Product\Builder|\PHPUnit_Framework_MockObject_MockObject */
    protected $productBuilder;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $initializationHelper;

    protected function setUp()
    {
        $this->productBuilder = $this->getMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Builder::class,
            ['build'],
            [],
            '',
            false
        );
        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)->disableOriginalConstructor()
            ->setMethods(['addData', 'getTypeId', 'getStoreId', '__sleep', '__wakeup'])->getMock();
        $this->product->expects($this->any())->method('getTypeId')->will($this->returnValue('simple'));
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue('1'));
        $this->productBuilder->expects($this->any())->method('build')->will($this->returnValue($this->product));

        $this->resultPage = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultPageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultPage);

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

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $this->action = (new ObjectManager($this))->getObject(
            NewAction::class,
            [
                'context' => $this->initContext(),
                'productBuilder' => $this->productBuilder,
                'resultPageFactory' => $resultPageFactory,
                'resultForwardFactory' => $resultForwardFactory,
                'storeManager' => $storeManager,
            ]
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
}
