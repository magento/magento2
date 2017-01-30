<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class to test product validation  before save
 */
class ValidateTest extends \Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTest
{
    /** @var \Magento\Catalog\Controller\Adminhtml\Product\Validate */
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
    /** @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $productFactory;
    /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultJson;
    /** @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultJsonFactory;
    /** @var  ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->productBuilder = $this->getMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Builder::class,
            ['build'],
            [],
            '',
            false
        );
        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'addData', 'getSku', 'getTypeId', 'getStoreId', '__sleep', '__wakeup', 'getAttributes',
                'setAttributeSetId',
            ])
            ->getMock();
        $this->product->expects($this->any())->method('getTypeId')->will($this->returnValue('simple'));
        $this->product->expects($this->any())->method('getStoreId')->will($this->returnValue('1'));
        $this->product->expects($this->any())->method('getAttributes')->will($this->returnValue([]));
        $this->productBuilder->expects($this->any())->method('build')->will($this->returnValue($this->product));

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
        $this->resultRedirectFactory = $this->getMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultRedirect = $this->getMock(
            \Magento\Backend\Model\View\Result\Redirect::class,
            [],
            [],
            '',
            false
        );
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->initializationHelper = $this->getMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::class,
            [],
            [],
            '',
            false
        );

        $this->productFactory = $this->getMockBuilder(\Magento\Catalog\Model\ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productFactory->expects($this->any())->method('create')->willReturn($this->product);

        $this->resultJson = $this->getMock(\Magento\Framework\Controller\Result\Json::class, [], [], '', false);
        $this->resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultJsonFactory->expects($this->any())->method('create')->willReturn($this->resultJson);

        $additionalParams = ['resultRedirectFactory' => $this->resultRedirectFactory];
        $this->action = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Controller\Adminhtml\Product\Validate::class,
            [
                'context' => $this->initContext($additionalParams),
                'productBuilder' => $this->productBuilder,
                'resultPageFactory' => $resultPageFactory,
                'resultForwardFactory' => $resultForwardFactory,
                'initializationHelper' => $this->initializationHelper,
                'resultJsonFactory' => $this->resultJsonFactory,
                'productFactory' => $this->productFactory,
            ]
        );
    }

    public function testAttributeSetIsObtainedFromPostByDefault()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap([['set', null, 4]]);
        $this->request->expects($this->any())->method('getPost')->willReturnMap([['set', null, 9]]);
        $this->product->expects($this->once())->method('setAttributeSetId')->with(9);

        $this->action->execute();
    }

    public function testAttributeSetIsObtainedFromGetWhenThereIsNoOneInPost()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap([['set', null, 4]]);
        $this->request->expects($this->any())->method('getPost')->willReturnMap([['set', null, null]]);
        $this->product->expects($this->once())->method('setAttributeSetId')->with(4);

        $this->action->execute();
    }
}
