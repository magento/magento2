<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Attribute\Backend;

use Magento\Customer\Model\Attribute\Backend\Data\Boolean;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BooleanTest extends TestCase
{
    /**
     * @var Boolean
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new Boolean();
    }

    /**
     * @param mixed $value
     * @param mixed $defaultValue
     * @param string|mixed $result
     * */
    #[DataProvider('beforeSaveDataProvider')]
    public function testBeforeSave($value, $defaultValue, $result)
    {
        $attributeMock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getName', 'getDefaultValue']
        );
        $customerMock = $this->createMock(Customer::class);

        $this->model->setAttribute($attributeMock);

        $attributeMock->expects($this->once())
            ->method('getName')
            ->willReturn('attribute_name');
        $attributeMock->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn($defaultValue);

        $customerMock->expects($this->once())
            ->method('getData')
            ->with('attribute_name', null)
            ->willReturn($value);
        $customerMock->expects($this->once())
            ->method('setData')
            ->with('attribute_name', $result)
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->beforeSave($customerMock));
    }

    /**
     * @return array
     */
    public static function beforeSaveDataProvider()
    {
        return [
            [null, null, '0'],
            [null, '', '0'],
            [null, '0', '0'],
            [null, '1', '1'],
            [null, 'Yes', '1'],
            ['', null, '0'],
            ['0', null, '0'],
            ['0', '1', '0'],
            ['1', null, '1'],
            ['1', 'Yes', '1'],
            ['Yes', null, '1'],
            ['Yes', 'Yes', '1'],
        ];
    }
}
