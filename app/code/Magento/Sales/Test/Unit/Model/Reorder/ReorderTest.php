<?php

declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Reorder;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\CustomerCartResolver;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\GuestCart\GuestCartResolver;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Reorder\Reorder;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Sales\Helper\Reorder as ReorderHelper;

class ReorderTest extends \PHPUnit\Framework\TestCase
{

//    /**
//     * @var ObjectManger
//     */
//    private $objectManager;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactoryMock;

    /**
     * @var $cartRepositoryMock|MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var ReorderHelper|MockObject
     */
    private $reorderHelperMock;

    /**
     * @var \Psr\Log\LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var $customerCartProviderMock|MockObject
     */
    private $customerCartProviderMock;

    /**
     * @var $guestCartResolverMock|MockObject
     */
    private $guestCartResolverMock;

    /**
     * $var ProductCollectionFactory|MockObject
     */
    private $productCollectionFactoryMock;

    /**
     * @var Json|MockObject $serializer
     */
    private $jsonSerializer;

    /**
     * @var CustomerManagement|MockObject
     */
    private $customerManagementMock;

    function setUp(): void
    {
        parent::setUp();

//        $this->objectManager = new ObjectManager($this);

        $this->orderFactoryMock = $this->createPartialMock(OrderFactory::class, ['create', 'loadByIncrementIdAndStoreId']);

        $this->customerCartProviderMock = $this->createPartialMock(CustomerCartResolver::class, ['resolve']);

        $this->guestCartResolverMock = $this->createPartialMock(GuestCartResolver::class, []);

        $this->cartRepositoryMock = $this->createPartialMock(CartRepositoryInterface::class, []);

        $this->reorderHelperMock = $this->createPartialMock(ReorderHelper::class, []);

        $this->logger = $this->createPartialMock(\Psr\Log\LoggerInterface::class,[]);

        $this->productCollectionFactoryMock = $this->createPartialMock(ProductCollectionFactory::class, []);

        $this->customerManagementMock = $this->createPartialMock(CustomerManagement::class,['getCartForCustomer']);



        $this->jsonSerializer = $this->getMockBuilder(Json::class)
            ->onlyMethods(['unserialize'])
            ->getMock();

    }

    public function testExecuteMethodWithCustomProductOptions()
    {
        $orderNumber = '1';
        $storeId    ='1';
        $customerId ='1';

//Shift+f6
        $reorderModel = new Reorder(
            $this->orderFactoryMock,
            $this->customerCartProviderMock,
            $this->guestCartResolverMock,
            $this->cartRepositoryMock,
            $this->reorderHelperMock,
            $this->logger,
            $this->productCollectionFactoryMock,
            $this->jsonSerializer
        );

        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())
            ->method('load')
            ->with($orderNumber)
            ->willReturnSelf();

        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturnSelf();

        $this->orderFactoryMock->expects($this->once())
            ->method('loadByIncrementIdAndStoreId')
            ->with($orderNumber, $storeId)
            ->willReturn($orderMock);

        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderNumber);

        $orderMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);


        $cartMock = $this->createMock(Quote::class);

        $this->customerCartProviderMock->expects($this->once())
            ->method('resolve')
            ->with($customerId)
            ->willReturn( $cartMock);

//         $this->customerManagementMock->expects($this->once())
//                ->method('getCartForCustomer')
//             ->with($customerId)
//             ->willReturn($cartMock);

        $result = $reorderModel->execute($orderNumber, $storeId);
        print_r($result);
        $expectedResult = null;

    }

}
