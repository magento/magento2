<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Layout;
use Magento\GroupedProduct\Controller\Adminhtml\Edit\Popup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PopupTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Popup
     */
    protected $action;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var MockObject
     */
    protected $request;

    /**
     * @var MockObject
     */
    protected $factory;

    /**
     * @var MockObject
     */
    protected $registry;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Layout|MockObject
     */
    protected $resultLayoutMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->factory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->registry = $this->createMock(Registry::class);
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultLayoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_LAYOUT, [])
            ->willReturn($this->resultLayoutMock);

        $this->objectManager = new ObjectManager($this);
        $this->context = $this->objectManager->getObject(
            Context::class,
            [
                'request' => $this->request,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
        $this->action = $this->objectManager->getObject(
            Popup::class,
            [
                'context' => $this->context,
                'factory' => $this->factory,
                'registry' => $this->registry
            ]
        );
    }

    /**
     * @return void
     */
    public function testPopupActionNoProductId(): void
    {
        $storeId = 12;
        $typeId = 4;
        $productId = null;
        $setId = 0;
        $product = $this->createPartialMock(
            Product::class,
            ['setStoreId', 'setTypeId', 'setData', '__wakeup']
        );

        $this->factory->expects($this->once())->method('create')->willReturn($product);
        $product->expects($this->once())->method('setStoreId')->with($storeId);
        $product->expects($this->once())->method('setTypeId')->with($typeId);
        $product->expects($this->once())->method('setData')->with('_edit_mode', true);
        $this->request
            ->method('getParam')
            ->withConsecutive(['id'], ['store', 0], ['type'], ['set'])
            ->willReturnOnConsecutiveCalls($productId, $storeId, $typeId, $setId);
        $this->registry->expects($this->once())->method('register')->with('current_product', $product);

        $this->assertSame($this->resultLayoutMock, $this->action->execute());
    }

    /**
     * @return void
     */
    public function testPopupActionWithProductIdNoSetId(): void
    {
        $storeId = 12;
        $typeId = 4;
        $setId = 0;
        $productId = 399;
        $product = $this->createPartialMock(
            Product::class,
            ['setStoreId', 'setTypeId', 'setData', 'load', '__wakeup']
        );

        $this->factory->expects($this->once())->method('create')->willReturn($product);
        $product->expects($this->once())->method('setStoreId')->with($storeId);
        $product->expects($this->never())->method('setTypeId');
        $product->expects($this->once())->method('setData')->with('_edit_mode', true);
        $product->expects($this->once())->method('load')->with($productId);
        $this->request
            ->method('getParam')
            ->withConsecutive(['id'], ['store', 0], ['type'], ['set'])
            ->willReturnOnConsecutiveCalls($productId, $storeId, $typeId, $setId);
        $this->registry->expects($this->once())->method('register')->with('current_product', $product);

        $this->assertSame($this->resultLayoutMock, $this->action->execute());
    }
}
