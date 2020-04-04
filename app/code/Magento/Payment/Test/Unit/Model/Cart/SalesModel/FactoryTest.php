<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Cart\SalesModel;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Model\Cart\SalesModel\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /** @var Factory */
    private $model;

    /** @var ObjectManagerInterface|MockObject */
    private $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->model = new Factory($this->objectManagerMock);
    }

    /**
     * @param string $salesModelClass
     * @param string $expectedType
     * @dataProvider createDataProvider
     */
    public function testCreate($salesModelClass, $expectedType)
    {
        $salesModel = $this->createPartialMock($salesModelClass, ['__wakeup']);
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $expectedType,
            ['salesModel' => $salesModel]
        )->will(
            $this->returnValue('some value')
        );
        $this->assertEquals('some value', $this->model->create($salesModel));
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            [\Magento\Quote\Model\Quote::class, \Magento\Payment\Model\Cart\SalesModel\Quote::class],
            [\Magento\Sales\Model\Order::class, \Magento\Payment\Model\Cart\SalesModel\Order::class]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalid()
    {
        $this->model->create('any invalid');
    }
}
