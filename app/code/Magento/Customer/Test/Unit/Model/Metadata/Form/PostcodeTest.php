<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Api\Data\ValidationRuleInterface;
use Magento\Customer\Model\Metadata\Form\Postcode;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Phrase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Stdlib\StringUtils;

class PostcodeTest extends AbstractFormTestCase
{
    /** @var StringUtils */
    private StringUtils $stringHelper;

    /**
     * @var DirectoryHelper|MockObject
     */
    protected $directoryHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stringHelper = new StringUtils();
        $this->directoryHelper = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Create an instance of the class that is being tested
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     * @return Postcode
     */
    protected function getClass($value)
    {
        return new Postcode(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            $value,
            0,
            false,
            $this->directoryHelper,
            $this->stringHelper
        );
    }

    /**
     * @param string $value to assign to boolean
     * @param bool $expected text output
     * @param string $countryId
     * @param bool $isOptional
     *
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $expected, $countryId, $isOptional)
    {
        $storeLabel = 'Zip/Postal Code';
        $this->attributeMetadataMock->expects($this->atLeastOnce())
            ->method('getStoreLabel')
            ->willReturn($storeLabel);

        $this->directoryHelper->expects($this->once())
            ->method('isZipCodeOptional')
            ->willReturnMap([
                [$countryId, $isOptional],
            ]);

        $object = $this->getClass($value);
        $object->setExtractedData(['country_id' => $countryId]);

        $actual = $object->validateValue($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function validateValueDataProvider()
    {
        return [
            ['', ['"Zip/Postal Code" is a required value.'], 'US', false],
            ['90034', true, 'US', false],
            ['', true, 'IE', true],
            ['90034', true, 'IE', true],
        ];
    }

    /**
     * @param string|int|bool|null $value to assign to boolean
     * @param string|bool $expected text output
     * @dataProvider validateValueLengthDataProvider
     */
    public function testValidateValueLength($value, $expected)
    {
        $minTextLengthRule = $this->getMockBuilder(ValidationRuleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMockForAbstractClass();
        $minTextLengthRule->expects($this->any())
            ->method('getName')
            ->willReturn('min_text_length');
        $minTextLengthRule->expects($this->any())
            ->method('getValue')
            ->willReturn(5);

        $maxTextLengthRule = $this->getMockBuilder(ValidationRuleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMockForAbstractClass();
        $maxTextLengthRule->expects($this->any())
            ->method('getName')
            ->willReturn('max_text_length');
        $maxTextLengthRule->expects($this->any())
            ->method('getValue')
            ->willReturn(6);

        $inputValidationRule = $this->getMockBuilder(ValidationRuleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMockForAbstractClass();
        $inputValidationRule->expects($this->any())
            ->method('getName')
            ->willReturn('input_validation');
        $inputValidationRule->expects($this->any())
            ->method('getValue')
            ->willReturn('numeric');

        $validationRules = [
            'input_validation' => $inputValidationRule,
            'min_text_length' => $minTextLengthRule,
            'max_text_length' => $maxTextLengthRule,
        ];

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getValidationRules'
        )->willReturn(
            $validationRules
        );

        $sut = $this->getClass($value);
        $actual = $sut->validateValue($value);

        if (is_bool($actual)) {
            $this->assertEquals($expected, $actual);
        } else {
            if (is_array($actual)) {
                $actual = array_map(function ($message) {
                    return $message instanceof Phrase ? $message->__toString() : $message;
                }, $actual);
            }

            if (is_array($expected)) {
                foreach ($expected as $key => $row) {
                    $this->assertEquals($row, $actual[$key]);
                }
            } else {
                $this->assertContains($expected, $actual);
            }
        }
    }

    /**
     * @return array
     */
    public function validateValueLengthDataProvider(): array
    {
        return [
            'false' => [false, ['"" is a required value.', '"" length must be equal or greater than 5 characters.']],
            'empty' => ['', ['"" is a required value.', '"" length must be equal or greater than 5 characters.']],
            'null' => [null, ['"" is a required value.', '"" length must be equal or greater than 5 characters.']],
            'one' => [1, '"" length must be equal or greater than 5 characters.'],
            'L1' => ['6', '"" length must be equal or greater than 5 characters.'],
            'L2' => ['66', '"" length must be equal or greater than 5 characters.'],
            'L5' => ['66666', true],
            'thousand' => ['10000', true],
            'L6' => ['666666', true],
            'L7' => ['6666666', '"" length must be equal or less than 6 characters.'],
            'S1' => ['s',
                [
                    '"" length must be equal or greater than 5 characters.',
                    "notDigits" => '"" contains non-numeric characters.'
                ]
            ],
            'S6' => ['string', ["notDigits" => '"" contains non-numeric characters.']],
            'S7' => ['strings',
                [
                    '"" length must be equal or less than 6 characters.',
                    "notDigits" => '"" contains non-numeric characters.'
                ]
            ],
            'L6s' => ['66666s', ["notDigits" => '"" contains non-numeric characters.']],
        ];
    }
}
