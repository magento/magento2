<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Customer\Attribute\Backend\Password;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class PasswordTest extends TestCase
{
    use MockCreationTrait;

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
        $object = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getPassword', 'getPasswordConfirm']
        );

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

    /** */
    #[DataProvider('passwordNegativeDataProvider')]
    public function testBeforeSaveNegative($password)
    {
        $this->expectException(LocalizedException::class);

        /** @var DataObject|MockObject $object */
        $object = $this->createPartialMockWithReflection(DataObject::class, ['getPassword']);

        $object->expects($this->once())->method('getPassword')->willReturn($password);

        $this->testable->beforeSave($object);
    }

    public function testBeforeSavePositive()
    {
        $password = 'more-then-6';
        $passwordHash = 'password-hash';

        /** @var DataObject|MockObject $object */
        $object = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getPassword', 'setPasswordHash', 'hashPassword']
        );

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
     * @param mixed $randomValue
     */
    #[DataProvider('randomValuesProvider')]
    public function testCustomerGetPasswordAndGetPasswordConfirmAlwaysReturnsAString($randomValue)
    {
        /** @var Customer|MockObject $customer */
        $customer = $this->createPartialMock(Customer::class, ['getData']);

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
