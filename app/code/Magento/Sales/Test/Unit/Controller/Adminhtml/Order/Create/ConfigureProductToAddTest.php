<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
    use MockCreationTrait;

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
        // Initialize ObjectManager to avoid "ObjectManager isn't initialized" errors
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->prepareObjectManager();
        
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
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
        $this->productHelperMock = $this->createMock(Product::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->resultForwardFactoryMock = $this->createMock(ForwardFactory::class);
        $this->quoteSessionMock = $this->createPartialMockWithReflection(
            Quote::class,
            ['getStore', 'getCustomerId']
        );
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->onlyMethods(['getCode', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->compositeHelperMock = $this->getMockBuilder(Composite::class)
            ->onlyMethods(['renderConfigureResult'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->createMock(Layout::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

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
            ->willReturnCallback(fn($param) => match ([$param]) {
                [Quote::class] => $this->quoteSessionMock,
                [Composite::class] => $this->compositeHelperMock
            });
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
