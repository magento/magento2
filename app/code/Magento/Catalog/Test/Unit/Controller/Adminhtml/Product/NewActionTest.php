<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Controller\Adminhtml\Product\NewAction;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTest;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;

class NewActionTest extends ProductTest
{
    /** @var NewAction */
    protected $action;

    /** @var Page|MockObject */
    protected $resultPage;

    /** @var Forward|MockObject */
    protected $resultForward;

    /** @var Builder|MockObject */
    protected $productBuilder;

    /** @var Product|MockObject */
    protected $product;

    /**
     * @var Helper|MockObject
     */
    protected $initializationHelper;

    protected function setUp(): void
    {
        $this->productBuilder = $this->createPartialMock(
            Builder::class,
            ['build']
        );
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['addData', 'getTypeId', 'getStoreId', '__sleep'])->getMock();
        $this->product->expects($this->any())->method('getTypeId')->willReturn('simple');
        $this->product->expects($this->any())->method('getStoreId')->willReturn('1');
        $this->productBuilder->expects($this->any())->method('build')->willReturn($this->product);

        $this->resultPage = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultPageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->resultForward = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultForwardFactory = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultForwardFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultForward);

        $this->action = (new ObjectManager($this))->getObject(
            NewAction::class,
            [
                'context' => $this->initContext(),
                'productBuilder' => $this->productBuilder,
                'resultPageFactory' => $resultPageFactory,
                'resultForwardFactory' => $resultForwardFactory,
            ]
        );
    }

    public function testExecute()
    {
        $this->action->getRequest()->expects($this->any())->method('getParam')->willReturn(true);
        $this->action->getRequest()->expects($this->any())->method('getFullActionName')
            ->willReturn('catalog_product_new');
        $this->action->execute();
    }
}
