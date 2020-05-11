<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\TaxClass;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\TaxClass\Factory;
use Magento\Tax\Model\TaxClass\Type\Customer;
use Magento\Tax\Model\TaxClass\Type\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param string $classType
     * @param string $className
     * @param MockObject $classTypeMock
     */
    public function testCreate($classType, $className, $classTypeMock)
    {
        $classMock = $this->createPartialMock(
            ClassModel::class,
            ['getClassType', 'getId', '__wakeup']
        );
        $classMock->expects($this->once())->method('getClassType')->willReturn($classType);
        $classMock->expects($this->once())->method('getId')->willReturn(1);

        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            ['data' => ['id' => 1]]
        )->willReturn(
            $classTypeMock
        );

        $taxClassFactory = new Factory($objectManager);
        $this->assertEquals($classTypeMock, $taxClassFactory->create($classMock));
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        $customerClassMock = $this->createMock(Customer::class);
        $productClassMock = $this->createMock(Product::class);
        return [
            [
                ClassModel::TAX_CLASS_TYPE_CUSTOMER,
                Customer::class,
                $customerClassMock,
            ],
            [
                ClassModel::TAX_CLASS_TYPE_PRODUCT,
                Product::class,
                $productClassMock
            ]
        ];
    }

    public function testCreateWithWrongClassType()
    {
        $wrongClassType = 'TYPE';
        $classMock = $this->createPartialMock(
            ClassModel::class,
            ['getClassType', 'getId', '__wakeup']
        );
        $classMock->expects($this->once())->method('getClassType')->willReturn($wrongClassType);

        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $taxClassFactory = new Factory($objectManager);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(sprintf('Invalid type of tax class "%s"', $wrongClassType));
        $taxClassFactory->create($classMock);
    }
}
