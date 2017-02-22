<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Address;

/**
 * Class ValidatorTest
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Address\Validator
     */
    protected $validator;

    /**
     * @var \Magento\Sales\Model\Order\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryFactoryMock;

    /**
     * Mock order address model
     */
    public function setUp()
    {
        $this->addressMock = $this->getMock(
            'Magento\Sales\Model\Order\Address',
            ['hasData', 'getEmail', 'getAddressType', '__wakeup'],
            [],
            '',
            false
        );
        $this->directoryHelperMock = $this->getMock(
            'Magento\Directory\Helper\Data',
            [],
            [],
            '',
            false
        );
        $this->countryFactoryMock = $this->getMock(
            'Magento\Directory\Model\CountryFactory',
            [],
            [],
            '',
            false
        );
        $this->validator = new \Magento\Sales\Model\Order\Address\Validator(
            $this->directoryHelperMock,
            $this->countryFactoryMock
        );
    }

    /**
     * Run test validate
     *
     * @param $addressData
     * @param $email
     * @param $addressType
     * @param $expectedWarnings
     * @dataProvider providerAddressData
     */
    public function testValidate($addressData, $email, $addressType, $expectedWarnings)
    {
        $this->addressMock->expects($this->any())
            ->method('hasData')
            ->will($this->returnValueMap($addressData));
        $this->addressMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($email));
        $this->addressMock->expects($this->once())
            ->method('getAddressType')
            ->will($this->returnValue($addressType));
        $actualWarnings = $this->validator->validate($this->addressMock);
        $this->assertEquals($expectedWarnings, $actualWarnings);
    }

    /**
     * Provides address data for tests
     *
     * @return array
     */
    public function providerAddressData()
    {
        return [
            [
                [
                    ['parent_id', true],
                    ['postcode', true],
                    ['lastname', true],
                    ['street', true],
                    ['city', true],
                    ['email', true],
                    ['telephone', true],
                    ['country_id', true],
                    ['firstname', true],
                    ['address_type', true],
                ],
                'co@co.co',
                'billing',
                [],
            ],
            [
                [
                    ['parent_id', true],
                    ['postcode', true],
                    ['lastname', true],
                    ['street', false],
                    ['city', true],
                    ['email', true],
                    ['telephone', true],
                    ['country_id', true],
                    ['firstname', true],
                    ['address_type', true],
                ],
                'co.co.co',
                'coco-shipping',
                [
                    'Street is a required field',
                    'Email has a wrong format',
                    'Address type doesn\'t match required options'
                ]
            ]
        ];
    }
}
