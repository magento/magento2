<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Validator;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $countryFactoryMock;

    /**
     * @var MockObject
     */
    protected $itemMock;

    /**
     * @var MockObject
     */
    protected $countryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->countryFactoryMock = $this->createMock(CountryFactory::class);
        $this->countryMock = $this->createMock(Country::class);
        $this->itemMock = $this->createPartialMock(
            Address::class,
            ['getCountryId', 'getEmail']
        );
        $this->countryFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->countryMock);
        $this->model = $objectManager->getObject(
            Validator::class,
            [
                'countryFactory' => $this->countryFactoryMock,
            ]
        );
    }

    public function testValidateWithEmptyObject()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->willReturn(null);
        $this->itemMock->expects($this->once())->method('getCountryId')->willReturn(null);
        $this->assertTrue($this->model->isValid($this->itemMock));
        $this->assertEmpty($this->model->getMessages());
    }

    public function testValidateWithInvalidEmail()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->willReturn('invalid_email');
        $this->itemMock->expects($this->once())->method('getCountryId')->willReturn(null);
        $this->assertFalse($this->model->isValid($this->itemMock));
        $messages = ['invalid_email_format' => 'Invalid email format'];
        $this->assertEquals($messages, $this->model->getMessages());
    }

    public function testValidateWithInvalidCountryId()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->willReturn(null);
        $this->itemMock->expects($this->once())->method('getCountryId')->willReturn(100);

        $this->countryMock->expects($this->once())->method('load')->with(100);
        $this->countryMock->expects($this->once())->method('getId')->willReturn(null);

        $this->assertFalse($this->model->isValid($this->itemMock));
        $messages = ['invalid_country_code' => 'Invalid country code'];
        $this->assertEquals($messages, $this->model->getMessages());
    }

    public function testValidateWithInvalidData()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->willReturn('invalid_email');
        $this->itemMock->expects($this->once())->method('getCountryId')->willReturn(100);

        $this->countryMock->expects($this->once())->method('load')->with(100);
        $this->countryMock->expects($this->once())->method('getId')->willReturn(null);

        $this->assertFalse($this->model->isValid($this->itemMock));
        $messages = [
            'invalid_email_format' => 'Invalid email format',
            'invalid_country_code' => 'Invalid country code',
        ];
        $this->assertEquals($messages, $this->model->getMessages());
    }

    public function testValidateWithValidData()
    {
        $this->itemMock->expects($this->once())->method('getEmail')->willReturn('test@example.com');
        $this->itemMock->expects($this->once())->method('getCountryId')->willReturn(100);

        $this->countryMock->expects($this->once())->method('load')->with(100);
        $this->countryMock->expects($this->once())->method('getId')->willReturn(100);

        $this->assertTrue($this->model->isValid($this->itemMock));
        $this->assertEmpty($this->model->getMessages());
    }
}
