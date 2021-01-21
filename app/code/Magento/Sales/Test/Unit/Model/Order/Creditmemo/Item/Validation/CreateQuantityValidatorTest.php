<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Item\Validation;

use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo\Item\Validation\CreationQuantityValidator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;

/**
 * Class CreateQuantityValidatorTest
 */
class CreateQuantityValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderItemRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderItemRepositoryMock;

    /**
     * @var Item|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderItemMock;

    /**
     * @var CreationQuantityValidator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $createQuantityValidator;

    /**
     * @var OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contexMock;

    /**
     * @var \stdClass|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entity;

    protected function setUp(): void
    {
        $this->orderItemRepositoryMock = $this->getMockBuilder(OrderItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $this->orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->entity = $this->getMockBuilder(\stdClass::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderItemId', 'getQty'])
            ->getMock();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValidateCreditMemoProductItems($orderItemId, $expectedResult, $withContext = false)
    {
        if ($orderItemId) {
            $this->entity->expects($this->once())
                ->method('getOrderItemId')
                ->willReturn($orderItemId);

            $this->orderItemRepositoryMock->expects($this->once())
                ->method('get')
                ->with($orderItemId)
                ->willReturn($this->orderItemMock);
        } else {
            $this->entity->expects($this->once())
                ->method('getOrderItemId')
                ->willThrowException(new NoSuchEntityException());
        }

        $this->contexMock = null;
        if ($withContext) {
            $this->contexMock = $this->getMockBuilder(OrderInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();

            $this->entity->expects($this->once())
                ->method('getQty')
                ->willReturn(11);
        }

        $this->createQuantityValidator = new CreationQuantityValidator(
            $this->orderItemRepositoryMock,
            $this->contexMock
        );

        $this->assertEquals($expectedResult, $this->createQuantityValidator->validate($this->entity));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'testValidateCreditMemoProductItems' => [
                1,
                [__('The creditmemo contains product item that is not part of the original order.')],
            ],
            'testValidateWithException' => [
                null,
                [__('The creditmemo contains product item that is not part of the original order.')]
            ],
            'testValidateWithContext' => [
                1,
                [__('The quantity to refund must not be greater than the unrefunded quantity.')],
                true
            ],
        ];
    }
}
