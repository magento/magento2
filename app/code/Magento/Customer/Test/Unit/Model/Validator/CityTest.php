<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Validator\City;
use Magento\Security\Model\Validator\Pattern\CityValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * City validator tests
 */
class CityTest extends TestCase
{
    /**
     * @var CityValidator|MockObject
     */
    private MockObject $cityValidatorMock;

    /**
     * @var Address|MockObject
     */
    private MockObject $addressMock;

    /**
     * @var City
     */
    private City $cityValidator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->cityValidatorMock = $this->createMock(CityValidator::class);
        $this->addressMock = $this->createMock(Address::class);
        $this->cityValidator = new City($this->cityValidatorMock);
    }

    /**
     * Test for allowed punctuation characters in city names
     *
     * @param string $city
     * @param string $message
     * @return void
     * @dataProvider expectedPunctuationInCityDataProvider
     */
    public function testValidateCorrectPunctuationInCities(
        string $city,
        string $message
    ) {
        $this->addressMock->expects($this->once())->method('getCity')->willReturn($city);
        $this->cityValidatorMock->expects($this->once())->method('isValid')->with($city)->willReturn(true);

        $isValid = $this->cityValidator->isValid($this->addressMock);
        $this->assertTrue($isValid, $message);
    }

    /**
     * @return array
     */
    public function expectedPunctuationInCityDataProvider(): array
    {
        return [
            [
                'city' => 'New York',
                'message' => 'Spaces must be allowed in city names'
            ],
            [
                'city' => 'São Paulo',
                'message' => 'Accented characters and spaces must be allowed in city names'
            ],
            [
                'city' => 'St. Louis',
                'message' => 'Periods and spaces must be allowed in city names'
            ],
            [
                'city' => 'Москва',
                'message' => 'Unicode letters must be allowed in city names'
            ],
            [
                'city' => 'Moscow \'',
                'message' => 'Apostrophe characters must be allowed in city names'
            ],
            [
                'city' => 'St.-Pierre',
                'message' => 'Hyphens must be allowed in city names'
            ],
            [
                'city' => 'Offenbach (Main)',
                'message' => 'Parentheses must be allowed in city names'
            ],
            [
                'city' => 'Rome: The Eternal City',
                'message' => 'Colons must be allowed in city names'
            ],
        ];
    }
}
