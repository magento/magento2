<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Controller\Unsubscribe;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductAlert\Controller\Unsubscribe\Price;
use Magento\ProductAlert\Model\Price as PriceModel;
use Magento\ProductAlert\Model\PriceFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\ProductAlert\Controller\Unsubscribe\Price
 */
class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    private $priceController;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Manager|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var PriceModel|MockObject
     */
    private $priceModelMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->requestMock = $this->createMock(Http::class);
        $resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->messageManagerMock = $this->createMock(Manager::class);
        $this->productMock = $this->createMock(Product::class);
        $contextMock = $this->createMock(Context::class);
        $customerSessionMock = $this->createMock(Session::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $priceFactoryMock = $this->createMock(PriceFactory::class);

        $resultFactoryMock->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);
        $contextMock->method('getRequest')->willReturn($this->requestMock);
        $contextMock->method('getResultFactory')->willReturn($resultFactoryMock);
        $contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $storeMock->method('getId')->willReturn(1);
        $storeManagerMock->method('getStore')->willReturn($storeMock);
        $customerSessionMock->method('getCustomerId')->willReturn(1);

        $this->priceModelMock = $this->getMockBuilder(PriceModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByParam', 'getId', 'delete'])
            ->getMock();
        $this->priceModelMock->method('loadByParam')->willReturnSelf();
        $this->priceModelMock->method('getId')->willReturn(5);
        $this->priceModelMock->method('delete')->willReturnSelf();
        $priceFactoryMock->method('create')->willReturn($this->priceModelMock);

        $this->priceController = $objectManager->getObject(
            Price::class,
            [
                'context' => $contextMock,
                'customerSession' => $customerSessionMock,
                'productRepository' => $this->productRepositoryMock,
                'storeManager' => $storeManagerMock,
                'priceFactory' => $priceFactoryMock,
            ]
        );
    }

    public function testExecuteLoadsAlertWithStoreIdAndDeletes(): void
    {
        $productId = 9;
        $this->requestMock->method('getParam')->with('product')->willReturn($productId);
        $this->productRepositoryMock->method('getById')->with($productId)->willReturn($this->productMock);
        $this->productMock->method('isVisibleInCatalog')->willReturn(true);
        $this->productMock->method('getId')->willReturn($productId);

        $this->priceModelMock->expects($this->once())->method('delete');
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You deleted the alert subscription.'));
        $this->resultRedirectMock->expects($this->once())->method('setPath')->with('productalert/customer/index');

        $this->assertSame($this->resultRedirectMock, $this->priceController->execute());
    }
}
