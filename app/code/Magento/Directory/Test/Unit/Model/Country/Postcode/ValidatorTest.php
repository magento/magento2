<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Country\Postcode;

use Magento\Directory\Model\Country\Postcode\Config;
use Magento\Directory\Model\Country\Postcode\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for postcode Validator (country-specific postcode validation).
 *
 * @covers \Magento\Directory\Model\Country\Postcode\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private MockObject $postcodesConfigMock;

    /**
     * @var Validator
     */
    private Validator $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->postcodesConfigMock = $this->createMock(Config::class);
        $postCodes = [
            'US' => [
                'pattern_1' => ['pattern' => '^[0-9]{5}\-[0-9]{4}$'],
                'pattern_2' => ['pattern' => '^[0-9]{5}$']
            ],
            'NL' => [
                'pattern_1' => ['pattern' => '^[1-9][0-9]{3}\s?[a-zA-Z]{2}$'],
                'pattern_2' => ['pattern' => '^[1-9][0-9]{3}$']
            ]
        ];
        $this->postcodesConfigMock->expects($this->atLeastOnce())
            ->method('getPostCodes')
            ->willReturn($postCodes);
        $this->model = new Validator($this->postcodesConfigMock);
    }

    /**
     * Test validate returns true for valid postcode and country combinations.
     *
     * @covers \Magento\Directory\Model\Country\Postcode\Validator::validate
     * @param string $postCode
     * @param string $countryId
     * @return void
     */
    #[DataProvider('getValidPostcodesDataProvider')]
    public function testValidateReturnsTrueForValidPostcode(string $postCode, string $countryId): void
    {
        $this->assertSame(true, $this->model->validate($postCode, $countryId));
    }

    /**
     * Data provider for valid postcode/country combinations.
     *
     * @return array[]
     */
    public static function getValidPostcodesDataProvider(): array
    {
        return [
            'US full zip with dash' => ['postCode' => '12345-6789', 'countryId' => 'US'],
            'US 5-digit zip' => ['postCode' => '12345', 'countryId' => 'US'],
            'NL 4-digit only' => ['postCode' => '1234', 'countryId' => 'NL'],
            'NL 4-digit regression value' => ['postCode' => '7311', 'countryId' => 'NL'],
            'NL full format no space' => ['postCode' => '1234AB', 'countryId' => 'NL'],
            'NL full format with space' => ['postCode' => '1234 AB', 'countryId' => 'NL'],
        ];
    }

    /**
     * Test validate returns false for invalid postcode and country combinations.
     *
     * @covers \Magento\Directory\Model\Country\Postcode\Validator::validate
     * @param string $postCode
     * @param string $countryId
     * @return void
     */
    #[DataProvider('getInvalidPostcodesDataProvider')]
    public function testValidateReturnsFalseForInvalidPostcode(string $postCode, string $countryId): void
    {
        $this->assertSame(false, $this->model->validate($postCode, $countryId));
    }

    /**
     * Data provider for invalid postcode/country combinations (edge cases).
     *
     * @return array[]
     */
    public static function getInvalidPostcodesDataProvider(): array
    {
        return [
            'US non-numeric postcode' => ['postCode' => 'POST-CODE', 'countryId' => 'US'],
            'US too short' => ['postCode' => '1234', 'countryId' => 'US'],
            'US too long' => ['postCode' => '123456', 'countryId' => 'US'],
            'NL too few digits' => ['postCode' => '123', 'countryId' => 'NL'],
            'NL two digits only' => ['postCode' => '12', 'countryId' => 'NL'],
            'NL one letter suffix' => ['postCode' => '1234 A', 'countryId' => 'NL'],
            'NL three letter suffix' => ['postCode' => '1234 ABC', 'countryId' => 'NL'],
            'NL space without letters' => ['postCode' => '12 34', 'countryId' => 'NL'],
            'NL five digits' => ['postCode' => '12345', 'countryId' => 'NL'],
            'NL leading zero' => ['postCode' => '0234', 'countryId' => 'NL'],
            'NL letters only' => ['postCode' => 'ABCD', 'countryId' => 'NL'],
        ];
    }

    /**
     * Test validate throws InvalidArgumentException when country does not exist in config.
     *
     * @covers \Magento\Directory\Model\Country\Postcode\Validator::validate
     * @return void
     */
    public function testValidateThrowsExceptionWhenCountryDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided countryId does not exist.');

        $this->model->validate('12345-6789', 'QQ');
    }
}
