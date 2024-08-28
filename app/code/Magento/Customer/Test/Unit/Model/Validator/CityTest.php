<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\City;
use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\GlobalCityValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Customer city validator tests
 */
class CityTest extends TestCase
{
    /**
     * @var City
     */
    private City $cityValidator;

    /**
     * @var Customer|MockObject
     */
    private MockObject $customerMock;

    /**
     * @var GlobalCityValidator|MockObject
     */
    private MockObject $globalCityValidatorMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->globalCityValidatorMock = $this->createMock(GlobalCityValidator::class);
        $this->cityValidator = new City($this->globalCityValidatorMock);
        $this->customerMock = $this
            ->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCity'])
            ->getMock();
    }

    /**
     * Test for allowed punctuation characters in city names
     *
     * @param string $city
     * @param string $message
     * @return void
     * @dataProvider expectedPunctuationInCityDataProvider
     */
    public function testValidateCorrectPunctuationInCity(
        string $city,
        string $message
    ) {
        $this->customerMock->expects($this->once())->method('getCity')->willReturn($city);

        $this->globalCityValidatorMock->expects($this->once())
            ->method('isValidCity')
            ->with($city)
            ->willReturn(true);

        $isValid = $this->cityValidator->isValid($this->customerMock);
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
