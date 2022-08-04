<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Test\Unit\Model\Resolver;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\SalesGraphQl\Model\Resolver\Orders;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\Order;

class OrdersTest extends TestCase
{
    /**
     * @var Orders
     */
    private $resolver;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var CollectionFactoryInterface|MockObject
     */
    private $collectionFactoryInterfaceMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);

        $this->collectionFactoryInterfaceMock = $this->getMockBuilder(CollectionFactoryInterface::class)
            ->getMock();

        $this->resolver = new Orders(
            $this->collectionFactoryInterfaceMock,
        );
    }

    public function testResolve(): void
    {
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock = $this->createMock(Order::class);

        $collectionMock->method('getIterator')
            ->willReturn(new \ArrayObject([$orderMock]));

        $this->contextMock
            ->method('getUserId')
            ->willReturn(1);

        $this->collectionFactoryInterfaceMock
            ->method('create')
            ->willReturn($collectionMock);

        $result = $this->resolver->resolve(
            $this->getFieldStub(),
            $this->contextMock,
            $this->getResolveInfoStub(),
            null
        );

        $this->assertIsArray($result);
        $assertKeys = [
            'id',
            'increment_id',
            'order_number',
            'created_at',
            'grand_total',
            'status',
            'model'
        ];
        foreach ($assertKeys as $key) {
            $this->assertArrayHasKey($key, current($result['items']));
        }
        $this->assertEquals($orderMock, current($result['items'])['model']);
    }

    /**
     * @return MockObject|Field
     */
    private function getFieldStub(): Field
    {
        return $this->createMock(Field::class);
    }

    /**
     * @return MockObject|ResolveInfo
     */
    private function getResolveInfoStub(): ResolveInfo
    {
        /** @var MockObject|ResolveInfo $resolveInfoMock */
        return $this->createMock(ResolveInfo::class);
    }
}
