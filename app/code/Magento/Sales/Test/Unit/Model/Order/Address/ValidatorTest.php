<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Address;

use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Validator\EmailAddress;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Address|MockObject
     */
    protected $addressMock;

    /**
     * @var Data|MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var CountryFactory|MockObject
     */
    protected $countryFactoryMock;

    /**
     * @var EmailAddress|MockObject
     */
    private $emailValidatorMock;

    /**
     * Mock order address model
     */
    protected function setUp(): void
    {
        $this->addressMock = $this->createPartialMock(
            Address::class,
            ['hasData', 'getEmail', 'getAddressType']
        );
        $this->directoryHelperMock = $this->createMock(Data::class);
        $this->countryFactoryMock = $this->createMock(CountryFactory::class);
        $eavConfigMock = $this->createMock(Config::class);
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())
            ->method('getIsRequired')
            ->willReturn(true);
        $eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attributeMock);
        $this->emailValidatorMock = $this->createMock(EmailAddress::class);
        $this->validator = new Validator(
            $this->directoryHelperMock,
            $this->countryFactoryMock,
            $eavConfigMock,
            $this->emailValidatorMock
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
            ->willReturnMap($addressData);
        $this->addressMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);
        $this->addressMock->expects($this->once())
            ->method('getAddressType')
            ->willReturn($addressType);
        $this->emailValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($email)
            ->willReturn((stripos($email, '@') !== false));
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
                    ['company', 'Magento'],
                    ['fax', '222-22-22'],
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
                    ['company', 'Magento'],
                    ['fax', '222-22-22'],
                ],
                'co.co.co',
                'coco-shipping',
                [
                    '"Street" is required. Enter and try again.',
                    'Email has a wrong format',
                    'Address type doesn\'t match required options'
                ]
            ]
        ];
    }
}
