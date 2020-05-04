<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Controller\Unsubscribe;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductAlert\Controller\Unsubscribe\Price;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\ProductAlert\Controller\Unsubscribe\Price
 */
class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    private $priceController;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Manager|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->requestMock = $this->createMock(Http::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->messageManagerMock = $this->createMock(Manager::class);
        $this->productMock = $this->createMock(Product::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactoryMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->priceController = $this->objectManager->getObject(
            Price::class,
            [
                'context' => $this->contextMock,
                'customerSession' => $this->customerSessionMock,
                'productRepository' => $this->productRepositoryMock,
            ]
        );
    }

    public function testProductIsNotVisibleInCatalog()
    {
        $productId = 123;
        $this->requestMock->expects($this->any())->method('getParam')->with('product')->willReturn($productId);
        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($productId)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())->method('isVisibleInCatalog')->willReturn(false);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__("The product wasn't found. Verify the product and try again."));
        $this->resultRedirectMock->expects($this->once())->method('setPath')->with('customer/account/');

        $this->assertEquals(
            $this->resultRedirectMock,
            $this->priceController->execute()
        );
    }
}
