<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Customer\Model\Customer\Attribute\Backend\Password;

class PasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Password
     */
    protected $testable;

    public function setUp()
    {
        $string = new StringUtils();
        $this->testable = new \Magento\Customer\Model\Customer\Attribute\Backend\Password($string);
    }

    public function testValidatePositive()
    {
        $password = 'password';
        $object = $this->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'getPasswordConfirm'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->will($this->returnValue($password));
        $object->expects($this->once())->method('getPasswordConfirm')->will($this->returnValue($password));
        /** @var \Magento\Framework\DataObject $object */

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
        $object = $this->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->setMethods(['getPassword'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->will($this->returnValue($password));
        /** @var \Magento\Framework\DataObject $object */

        $this->testable->beforeSave($object);
    }

    public function testBeforeSavePositive()
    {
        $password = 'more-then-6';
        $passwordHash = 'password-hash';
        $object = $this->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'setPasswordHash', 'hashPassword'])
            ->getMock();

        $object->expects($this->once())->method('getPassword')->will($this->returnValue($password));
        $object->expects($this->once())->method('hashPassword')->will($this->returnValue($passwordHash));
        $object->expects($this->once())->method('setPasswordHash')->with($passwordHash)->will($this->returnSelf());
        /** @var \Magento\Framework\DataObject $object */

        $this->testable->beforeSave($object);
    }
}
