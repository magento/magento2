<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\TaxClass;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\TaxClass\Factory;
use Magento\Tax\Model\TaxClass\Type\Customer;
use Magento\Tax\Model\TaxClass\Type\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    #[DataProvider('createDataProvider')]
    public function testCreate(string $classType, string $className, \Closure $classTypeMock): void
    {
        $classTypeMock = $classTypeMock($this);
        $classMock = $this->createPartialMock(
            ClassModel::class,
            ['getClassType', 'getId', '__wakeup']
        );
        $classMock->expects($this->once())->method('getClassType')->willReturn($classType);
        $classMock->expects($this->once())->method('getId')->willReturn(1);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
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
    public static function createDataProvider(): array
    {
        $customerClassMock = static fn (self $testCase) =>
            $testCase->createMock(Customer::class);
        $productClassMock = static fn (self $testCase) =>
            $testCase->createMock(Product::class);
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

    public function testCreateWithWrongClassType(): void
    {
        $wrongClassType = 'TYPE';
        $classMock = $this->createPartialMock(
            ClassModel::class,
            ['getClassType', 'getId', '__wakeup']
        );
        $classMock->expects($this->once())->method('getClassType')->willReturn($wrongClassType);

        $objectManager = $this->createMock(ObjectManagerInterface::class);

        $taxClassFactory = new Factory($objectManager);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(sprintf('Invalid type of tax class "%s"', $wrongClassType));
        $taxClassFactory->create($classMock);
    }
}
