<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);

=======
>>>>>>> upstream/2.2-develop
namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type\Multishipping;

use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderPool;

<<<<<<< HEAD
/**
 * Tests Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderPool.
 */
=======
>>>>>>> upstream/2.2-develop
class PlaceOrderPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $paymentProviderCode
     * @param PlaceOrderInterface[] $placeOrderList
     * @param PlaceOrderInterface|null $expectedResult
<<<<<<< HEAD
     * @return void
=======
>>>>>>> upstream/2.2-develop
     *
     * @dataProvider getDataProvider
     */
    public function testGet(string $paymentProviderCode, array $placeOrderList, $expectedResult)
    {
        /** @var TMapFactory|\PHPUnit_Framework_MockObject_MockObject $tMapFactory */
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tMapFactory->method('createSharedObjectsMap')->willReturn($placeOrderList);

        $placeOrderPool = new PlaceOrderPool($tMapFactory);
        $result = $placeOrderPool->get($paymentProviderCode);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getDataProvider(): array
    {
        $placeOrder = $this->getMockForAbstractClass(PlaceOrderInterface::class);
        $placeOrderList = ['payment_code' => $placeOrder];

        return [
            'code exists in pool' => ['payment_code', $placeOrderList, $placeOrder],
            'no code in pool' => ['some_code', $placeOrderList, null],
        ];
    }
}
