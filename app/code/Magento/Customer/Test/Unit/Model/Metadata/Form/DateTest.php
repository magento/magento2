<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\ValidationRuleInterface;
use Magento\Customer\Model\Metadata\Form\Date;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DateTest extends AbstractFormTestCase
{
    /** @var \Magento\Customer\Model\Metadata\Form\Date */
    protected $date;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->willReturn(
            'date'
        );
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getStoreLabel'
        )->willReturn(
            'Space Date'
        );
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getInputFilter'
        )->willReturn(
            'date'
        );
        $this->date = new Date(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            null,
            0
        );
    }

    /**
     * Test extractValue
     */
    public function testExtractValue()
    {
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())->method('getParam')->willReturn('1999-1-2');

        // yyyy-MM-dd
        $actual = $this->date->extractValue($requestMock);
        $this->assertEquals('1999-01-02', $actual);
    }

    /**
     * Test extractValue without inputFilter set
     */
    public function testExtractValueWithoutInputFilter()
    {
        /* local version of locale */
        $localeMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localeMock->expects($this->any())->method('getDateFormat')->willReturn('d/M/yy');

        /* local version of attribute meta data */
        $attributeMetadataMock = $this->getMockForAbstractClass(AttributeMetadataInterface::class);
        $attributeMetadataMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('date');
        $attributeMetadataMock->expects($this->any())
            ->method('getStoreLabel')
            ->willReturn('Space Date');
        $attributeMetadataMock->expects($this->any())
            ->method('getInputFilter')
            ->willReturn(null);
        $attributeMetadataMock->expects($this->any())
            ->method('isUserDefined')
            ->willReturn(true);
        $attributeMetadataMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('date');

        $date = new Date(
            $localeMock,
            $this->loggerMock,
            $attributeMetadataMock,
            $this->localeResolverMock,
            null,
            0
        );

        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())->method('getParam')->willReturn('01/2/1999');

        $actual = $date->extractValue($requestMock);
        $this->assertEquals('1999-02-01', $actual);
    }

    /**
     * @param array|string $value Value to validate
     * @param array $validation Array of more validation metadata
     * @param bool $required Whether field is required
     * @param array|bool $expected Expected output
     *
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $validation, $required, $expected)
    {
        $validationRules = [];
        $validationRule = $this->getMockBuilder(ValidationRuleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMockForAbstractClass();
        $validationRule->expects($this->any())
            ->method('getName')
            ->willReturn('input_validation');
        $validationRule->expects($this->any())
            ->method('getValue')
            ->willReturn('date');

        $validationRules[] = $validationRule;
        if (is_array($validation)) {
            foreach ($validation as $ruleName => $ruleValue) {
                $validationRule = $this->getMockBuilder(ValidationRuleInterface::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['getName', 'getValue'])
                    ->getMockForAbstractClass();
                $validationRule->expects($this->any())
                    ->method('getName')
                    ->willReturn($ruleName);
                $validationRule->expects($this->any())
                    ->method('getValue')
                    ->willReturn($ruleValue);

                $validationRules[] = $validationRule;
            }
        }

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getValidationRules'
        )->willReturn(
            $validationRules
        );

        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->willReturn($required);

        $actual = $this->date->validateValue($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function validateValueDataProvider()
    {
        return [
            'false value, load original' => [false, [], false, true],
            'Empty value, not required' => ['', [], false, true],
            'Empty value, required' => ['', [], true, ['"Space Date" is a required value.']],
            'Valid date, min set' => ['1961-5-5', ['date_range_min' => strtotime('4/12/1961')], false, true],
            'Below min, only min set' => [
                '1957-10-4',
                ['date_range_min' => strtotime('1961/04/12')],
                false,
                ['Please enter a valid date equal to or greater than 12/04/1961 at Space Date.'],
            ],
            'Below min, min and max set' => [
                '1957-10-4',
                ['date_range_min' => strtotime('1961/04/12'), 'date_range_max' => strtotime('12/1/2013')],
                false,
                ['Please enter a valid date between 12/04/1961 and 01/12/2013 at Space Date.'],
            ],
            'Above max, only max set' => [
                '2014-1-30',
                ['date_range_max' => strtotime('12/1/2013')],
                false,
                ['Please enter a valid date less than or equal to 01/12/2013 at Space Date.'],
            ],
            'Valid, min and max' => [
                '1961-5-5',
                ['date_range_min' => strtotime('4/12/1961'), 'date_range_max' => strtotime('12/1/2013')],
                false,
                true,
            ],
            'Invalid date' => [
                'abc',
                [],
                false,
                ['dateInvalidDate' => '"Space Date" is not a valid date.'],
            ]
        ];
    }

    /**
     * @param array|string $value value to pass to compactValue()
     * @param array|string|bool $expected expected output
     *
     * @dataProvider compactAndRestoreValueDataProvider
     */
    public function testCompactValue($value, $expected)
    {
        $this->assertSame($expected, $this->date->compactValue($value));
    }

    /**
     * @return array
     */
    public function compactAndRestoreValueDataProvider()
    {
        return [
            [1, 1],
            [false, false],
            [null, null],
            ['test', 'test'],
            [['element1', 'element2'], ['element1', 'element2']]
        ];
    }

    /**
     * @param array|string $value Value to pass to restoreValue()
     * @param array|string|bool $expected Expected output
     *
     * @dataProvider compactAndRestoreValueDataProvider
     */
    public function testRestoreValue($value, $expected)
    {
        $this->assertSame($expected, $this->date->restoreValue($value));
    }

    /**
     * Test outputValue
     */
    public function testOutputValue()
    {
        $this->assertNull($this->date->outputValue());
        $date = new Date(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            '2012/12/31',
            0
        );
        $this->assertEquals('2012-12-31', $date->outputValue());
    }
}
