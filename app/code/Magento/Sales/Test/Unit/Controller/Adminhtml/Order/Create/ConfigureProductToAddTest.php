<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Controller\Adminhtml\Order\Create\ConfigureProductToAdd;
use Magento\Framework\View\Result\Layout;
use Magento\Store\Model\Store;
use Magento\Catalog\Helper\Product\Composite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Helper\Product;
use Magento\Framework\Escaper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

/**
 * Tests for \Magento\Sales\Controller\Adminhtml\Order\Create\ConfigureProductToAdd
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigureProductToAddTest extends TestCase
{
    /**
     * @var Context
     */
    private $contextMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ForwardFactory|MockObject
     */
    private $resultForwardFactoryMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteSessionMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var Composite|MockObject
     */
    private $compositeHelperMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Product|MockObject
     */
    private $productHelperMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var Layout|MockObject
     */
    private $layoutMock;

    /**
     * @var ConfigureProductToAdd
     */
    private $configureProductToAdd;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->onlyMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getObjectManager', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->productHelperMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteSessionMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getStore'])
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->onlyMethods(['getCode', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->compositeHelperMock = $this->getMockBuilder(Composite::class)
            ->onlyMethods(['renderConfigureResult'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->onlyMethods(['setCurrentStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configureProductToAdd = new ConfigureProductToAdd(
            $this->contextMock,
            $this->productHelperMock,
            $this->escaperMock,
            $this->resultPageFactoryMock,
            $this->resultForwardFactoryMock,
            $this->storeManagerMock
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $productId = 1;
        $customerId = 1;
        $storeCode = 'view2';
        $storeId = 2;
        $configureResult = new DataObject(
            [
                'ok' => true,
                'product_id' => $productId,
                'current_store_id' => $storeId,
                'current_customer_id' => $customerId,
            ]
        );
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($productId);
        $this->objectManagerMock
            ->method('get')
            ->withConsecutive([Quote::class], [Composite::class])
            ->willReturnOnConsecutiveCalls($this->quoteSessionMock, $this->compositeHelperMock);
        $this->quoteSessionMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->quoteSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn($storeCode);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with($storeCode)
            ->willReturnSelf();
        $this->compositeHelperMock->expects($this->once())
            ->method('renderConfigureResult')
            ->with($configureResult)->willReturn($this->layoutMock);

        $this->assertInstanceOf(Layout::class, $this->configureProductToAdd->execute());
    }
}
