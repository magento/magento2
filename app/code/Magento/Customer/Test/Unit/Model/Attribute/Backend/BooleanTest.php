<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Attribute\Backend;

use Magento\Customer\Model\Attribute\Backend\Data\Boolean;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
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
     *
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave($value, $defaultValue, $result)
    {
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getName', 'getDefaultValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

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
    public function beforeSaveDataProvider()
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
