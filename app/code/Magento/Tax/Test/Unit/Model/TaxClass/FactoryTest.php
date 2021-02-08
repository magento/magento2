<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\TaxClass;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param string $classType
     * @param string $className
     * @param \PHPUnit\Framework\MockObject\MockObject $classTypeMock
     */
    public function testCreate($classType, $className, $classTypeMock)
    {
        $classMock = $this->createPartialMock(
            \Magento\Tax\Model\ClassModel::class,
            ['getClassType', 'getId', '__wakeup']
        );
        $classMock->expects($this->once())->method('getClassType')->willReturn($classType);
        $classMock->expects($this->once())->method('getId')->willReturn(1);

        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($className),
            $this->equalTo(['data' => ['id' => 1]])
        )->willReturn(
            $classTypeMock
        );

        $taxClassFactory = new \Magento\Tax\Model\TaxClass\Factory($objectManager);
        $this->assertEquals($classTypeMock, $taxClassFactory->create($classMock));
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        $customerClassMock = $this->createMock(\Magento\Tax\Model\TaxClass\Type\Customer::class);
        $productClassMock = $this->createMock(\Magento\Tax\Model\TaxClass\Type\Product::class);
        return [
            [
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER,
                \Magento\Tax\Model\TaxClass\Type\Customer::class,
                $customerClassMock,
            ],
            [
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT,
                \Magento\Tax\Model\TaxClass\Type\Product::class,
                $productClassMock
            ]
        ];
    }

    public function testCreateWithWrongClassType()
    {
        $wrongClassType = 'TYPE';
        $classMock = $this->createPartialMock(
            \Magento\Tax\Model\ClassModel::class,
            ['getClassType', 'getId', '__wakeup']
        );
        $classMock->expects($this->once())->method('getClassType')->willReturn($wrongClassType);

        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $taxClassFactory = new \Magento\Tax\Model\TaxClass\Factory($objectManager);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(sprintf('Invalid type of tax class "%s"', $wrongClassType));
        $taxClassFactory->create($classMock);
    }
}
