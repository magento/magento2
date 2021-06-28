<?php

declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Reorder;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Reorder\Reorder;
use PHPUnit\Framework\MockObject\MockObject;


class ReorderTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ObjectManger
     */
    protected $objectManager;

    /**
     * @var OrderFactory|MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var Reorder $model
     */
    protected $reorderModel;

   /**
    * @var Json|MockObject $serializer
    */
    protected $jsonSerializer;

    function setUp(): void
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);

        $this->orderFactoryMock = $this->createPartialMock(OrderFactory::class,['create','loadByIncrementIdAndStoreId']);

        $this->jsonSerializer = $this->getMockBuilder(Json::class)
                                ->onlyMethods(['unserialize'])
                                ->getMock();

        $this->reorderModel = $this->objectManager->getObject(Reorder::class,
            [
                'orderFactory' => $this->orderFactoryMock,
                'serializer' => $this->jsonSerializer
            ]
        );

    }

    public function testExecuteMethodWithCustomProductOptions()
    {
        $orderNumber = '1';
        $storeId    ='1';
        $customerId ='1';


        $order = $this->createMock(Order::class);
        $order->expects($this->once())
            ->method('load')
            ->with($orderNumber)
            ->willReturnSelf();

        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturnSelf();

        $this->orderFactoryMock->expects($this->once())
            ->method('loadByIncrementIdAndStoreId')
            ->with($orderNumber, $storeId)
            ->willReturn($order);

        $order->expects($this->once())
            ->method('getId')
            ->willReturn($orderNumber);

        $order->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $result = $this->reorderModel->execute($orderNumber, $storeId);
        print_r($result);
        $expectedResult = null;

    }

}
