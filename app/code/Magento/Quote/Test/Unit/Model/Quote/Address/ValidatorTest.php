<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use \Magento\Quote\Model\Quote\Address\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Validator
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->countryFactoryMock = $this->getMock(\Magento\Directory\Model\CountryFactory::class, [], [], '', false);
        $this->countryMock = $this->getMock(\Magento\Directory\Model\Country::class, [], [], '', false);
        $this->itemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getCountryId', 'getEmail'],
            [],
            '',
            false
        );
        $this->countryFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->countryMock));
        $this->model = $objectManager->getObject(
            \Magento\Quote\Model\Quote\Address\Validator::class,
            [
                'countryFactory' => $this->countryFactoryMock,
            ]
        );
    }

    public function testValidateWithEmptyObject()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->will($this->returnValue(null));
        $this->itemMock->expects($this->once())->method('getCountryId')->will($this->returnValue(null));
        $this->assertTrue($this->model->isValid($this->itemMock));
        $this->assertEmpty($this->model->getMessages());
    }

    public function testValidateWithInvalidEmail()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->will($this->returnValue('invalid_email'));
        $this->itemMock->expects($this->once())->method('getCountryId')->will($this->returnValue(null));
        $this->assertFalse($this->model->isValid($this->itemMock));
        $messages = ['invalid_email_format' => 'Invalid email format'];
        $this->assertEquals($messages, $this->model->getMessages());
    }

    public function testValidateWithInvalidCountryId()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->will($this->returnValue(null));
        $this->itemMock->expects($this->once())->method('getCountryId')->will($this->returnValue(100));

        $this->countryMock->expects($this->once())->method('load')->with(100);
        $this->countryMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        $this->assertFalse($this->model->isValid($this->itemMock));
        $messages = ['invalid_country_code' => 'Invalid country code'];
        $this->assertEquals($messages, $this->model->getMessages());
    }

    public function testValidateWithInvalidData()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->will($this->returnValue('invalid_email'));
        $this->itemMock->expects($this->once())->method('getCountryId')->will($this->returnValue(100));

        $this->countryMock->expects($this->once())->method('load')->with(100);
        $this->countryMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        $this->assertFalse($this->model->isValid($this->itemMock));
        $messages = [
            'invalid_email_format' => 'Invalid email format',
            'invalid_country_code' => 'Invalid country code',
        ];
        $this->assertEquals($messages, $this->model->getMessages());
    }

    public function testValidateWithValidData()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->will($this->returnValue('test@example.com'));
        $this->itemMock->expects($this->once())->method('getCountryId')->will($this->returnValue(100));

        $this->countryMock->expects($this->once())->method('load')->with(100);
        $this->countryMock->expects($this->once())->method('getId')->will($this->returnValue(100));

        $this->assertTrue($this->model->isValid($this->itemMock));
        $this->assertEmpty($this->model->getMessages());
    }
}
