<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Customer\Model\Customer\Attribute\Backend\Password;

class PasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Password
     */
    protected $testable;

    protected function setUp()
    {
        $string = new StringUtils();
        $this->testable = new \Magento\Customer\Model\Customer\Attribute\Backend\Password($string);
    }

    public function testValidatePositive()
    {
        $password = 'password';

        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'getPasswordConfirm'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->willReturn($password);
        $object->expects($this->once())->method('getPasswordConfirm')->willReturn($password);

        $this->assertTrue($this->testable->validate($object));
    }

    public function passwordNegativeDataProvider()
    {
        return [
            'less-then-6-char' => ['less6'],
            'with-space-prefix' => [' normal_password'],
            'with-space-suffix' => ['normal_password '],
        ];
    }

    /**
     * @dataProvider passwordNegativeDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testBeforeSaveNegative($password)
    {
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPassword'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->willReturn($password);

        $this->testable->beforeSave($object);
    }

    public function testBeforeSavePositive()
    {
        $password = 'more-then-6';
        $passwordHash = 'password-hash';

        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'setPasswordHash', 'hashPassword'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->willReturn($password);
        $object->expects($this->once())->method('hashPassword')->willReturn($passwordHash);
        $object->expects($this->once())->method('setPasswordHash')->with($passwordHash)->willReturnSelf();

        $this->testable->beforeSave($object);
    }

    /**
     * @return array
     */
    public function randomValuesProvider()
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
        /** @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $customer->expects($this->exactly(2))->method('getData')->willReturn($randomValue);

        $this->assertInternalType(
            'string',
            $customer->getPassword(),
            'Customer password should always return a string'
        );

        $this->assertInternalType(
            'string',
            $customer->getPasswordConfirm(),
            'Customer password-confirm should always return a string'
        );
    }
}
