<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Customer\Attribute\Backend\Password;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    /**
     * @var Password
     */
    protected $testable;

    protected function setUp(): void
    {
        $string = new StringUtils();
        $this->testable = new Password($string);
    }

    public function testValidatePositive()
    {
        $password = 'password';

        /** @var DataObject|MockObject $object */
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPassword', 'getPasswordConfirm'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->willReturn($password);
        $object->expects($this->once())->method('getPasswordConfirm')->willReturn($password);

        $this->assertTrue($this->testable->validate($object));
    }

    /**
     * @return array
     */
    public static function passwordNegativeDataProvider()
    {
        return [
            'less-then-6-char' => ['less6'],
            'with-space-prefix' => [' normal_password'],
            'with-space-suffix' => ['normal_password '],
        ];
    }

    /**
     * @dataProvider passwordNegativeDataProvider
     */
    public function testBeforeSaveNegative($password)
    {
        $this->expectException(LocalizedException::class);

        /** @var DataObject|MockObject $object */
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPassword'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->willReturn($password);

        $this->testable->beforeSave($object);
    }

    public function testBeforeSavePositive()
    {
        $password = 'more-then-6';
        $passwordHash = 'password-hash';

        /** @var DataObject|MockObject $object */
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPassword', 'setPasswordHash', 'hashPassword'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->willReturn($password);
        $object->expects($this->once())->method('hashPassword')->willReturn($passwordHash);
        $object->expects($this->once())->method('setPasswordHash')->with($passwordHash)->willReturnSelf();

        $this->testable->beforeSave($object);
    }

    /**
     * @return array
     */
    public static function randomValuesProvider()
    {
        return [
            [false],
            [1],
            ["23"],
            [null],
            [""],
            [-1],
            [12.3],
            [true],
            [0],
        ];
    }

    /**
     * @dataProvider randomValuesProvider
     * @param mixed $randomValue
     */
    public function testCustomerGetPasswordAndGetPasswordConfirmAlwaysReturnsAString($randomValue)
    {
        /** @var Customer|MockObject $customer */
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();

        $customer->expects($this->exactly(2))->method('getData')->willReturn($randomValue);

        $this->assertIsString(
            $customer->getPassword(),
            'Customer password should always return a string'
        );

        $this->assertIsString(
            $customer->getPasswordConfirm(),
            'Customer password-confirm should always return a string'
        );
    }
}
