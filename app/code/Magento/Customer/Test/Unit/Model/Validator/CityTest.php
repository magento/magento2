<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Validator\City;
use Magento\Customer\Model\Validator\Pattern\CityValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for validating city field in address.
 */
class CityTest extends TestCase
{
    /**
     * @var City
     */
    private City $cityValidator;

    /**
     * @var CityValidator|MockObject
     */
    private MockObject $cityValidatorMock;

    /**
     * @var Customer|MockObject
     */
    private MockObject $addressMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->cityValidatorMock = $this->createMock(CityValidator::class);
        $this->cityValidator = new City($this->cityValidatorMock);

        $this->addressMock = $this
            ->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCity'])
            ->getMock();
    }

    /**
     * Test for valid city name
     *
     * @param string $city
     * @param bool $expectedIsValid
     * @return void
     * @dataProvider expectedPunctuationInCityDataProvider
     */
    public function testValidateCityName(
        string $city,
        bool $expectedIsValid
    ): void {
        $this->addressMock->expects($this->once())->method('getCity')->willReturn($city);

        $isValid = $this->cityValidator->isValid($this->addressMock);
        $this->assertEquals($expectedIsValid, $isValid);
    }

    /**
     * Data provider for city names
     *
     * @return array
     */
    public static function expectedPunctuationInNamesDataProvider(): array
    {
        return [
            [
                'city' => 'New York',
                'expectedIsValid' => true,
                'message' => 'Spaces must be allowed in city names'
            ],
            [
                'city' => 'São Paulo',
                'expectedIsValid' => true,
                'message' => 'Accented characters and spaces must be allowed in city names'
            ],
            [
                'city' => 'St. Louis',
                'expectedIsValid' => true,
                'message' => 'Periods and spaces must be allowed in city names'
            ],
            [
                'city' => 'Москва',
                'expectedIsValid' => true,
                'message' => 'Unicode letters must be allowed in city names'
            ],
            [
                'city' => 'Moscow \'',
                'expectedIsValid' => true,
                'message' => 'Apostrophe characters must be allowed in city names'
            ],
            [
                'city' => 'St.-Pierre',
                'expectedIsValid' => true,
                'message' => 'Hyphens must be allowed in city names'
            ],
            [
                'city' => 'Offenbach (Main)',
                'expectedIsValid' => true,
                'message' => 'Parentheses must be allowed in city names'
            ],
            [
                'city' => 'Rome: The Eternal City',
                'expectedIsValid' => true,
                'message' => 'Colons must be allowed in city names'
            ],
        ];
    }
}
