<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Controller\Adminhtml\Edit;

use Magento\Framework\Controller\ResultFactory;

class PopupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\GroupedProduct\Controller\Adminhtml\Edit\Popup
     */
    protected $action;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultLayoutMock;

    protected function setUp()
    {
        $this->request = $this->getMock(\Magento\Framework\App\RequestInterface::class, [], [], '', false);
        $this->factory = $this->getMock(\Magento\Catalog\Model\ProductFactory::class, ['create'], [], '', false);
        $this->registry = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultLayoutMock = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_LAYOUT, [])
            ->willReturn($this->resultLayoutMock);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $this->objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->request,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
        $this->action = $this->objectManager->getObject(
            \Magento\GroupedProduct\Controller\Adminhtml\Edit\Popup::class,
            [
                'context' => $this->context,
                'factory' => $this->factory,
                'registry' => $this->registry
            ]
        );
    }

    public function testPopupActionNoProductId()
    {
        $storeId = 12;
        $typeId = 4;
        $productId = null;
        $setId = 0;
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['setStoreId', 'setTypeId', 'setData', '__wakeup'],
            [],
            '',
            false
        );

        $this->request->expects($this->at(0))->method('getParam')->with('id')->will($this->returnValue($productId));
        $this->factory->expects($this->once())->method('create')->will($this->returnValue($product));
        $this->request->expects(
            $this->at(1)
        )->method(
            'getParam'
        )->with(
            'store',
            0
        )->will(
            $this->returnValue($storeId)
        );

        $product->expects($this->once())->method('setStoreId')->with($storeId);
        $this->request->expects($this->at(2))->method('getParam')->with('type')->will($this->returnValue($typeId));
        $product->expects($this->once())->method('setTypeId')->with($typeId);
        $product->expects($this->once())->method('setData')->with('_edit_mode', true);
        $this->request->expects($this->at(3))->method('getParam')->with('set')->will($this->returnValue($setId));
        $this->registry->expects($this->once())->method('register')->with('current_product', $product);

        $this->assertSame($this->resultLayoutMock, $this->action->execute());
    }

    public function testPopupActionWithProductIdNoSetId()
    {
        $storeId = 12;
        $typeId = 4;
        $setId = 0;
        $productId = 399;
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['setStoreId', 'setTypeId', 'setData', 'load', '__wakeup'],
            [],
            '',
            false
        );

        $this->request->expects($this->at(0))->method('getParam')->with('id')->will($this->returnValue($productId));
        $this->factory->expects($this->once())->method('create')->will($this->returnValue($product));
        $this->request->expects(
            $this->at(1)
        )->method(
            'getParam'
        )->with(
            'store',
            0
        )->will(
            $this->returnValue($storeId)
        );
        $product->expects($this->once())->method('setStoreId')->with($storeId);
        $this->request->expects($this->at(2))->method('getParam')->with('type')->will($this->returnValue($typeId));
        $product->expects($this->never())->method('setTypeId');
        $product->expects($this->once())->method('setData')->with('_edit_mode', true);
        $product->expects($this->once())->method('load')->with($productId);
        $this->request->expects($this->at(3))->method('getParam')->with('set')->will($this->returnValue($setId));
        $this->registry->expects($this->once())->method('register')->with('current_product', $product);

        $this->assertSame($this->resultLayoutMock, $this->action->execute());
    }
}
