<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Eav text attribute model test
 */
class TextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\Text
     */
    private $model;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $locale = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $localeResolver = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $helper = new StringUtils;

        $this->model = new \Magento\Eav\Model\Attribute\Data\Text($locale, $logger, $localeResolver, $helper);
        $this->model->setAttribute(
            $this->createAttribute(
                [
                    'store_label' => 'Test',
                    'attribute_code' => 'test',
                    'is_required' => 1,
                    'validate_rules' => ['min_text_length' => 0, 'max_text_length' => 0, 'input_validation' => 0],
                ]
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $this->model = null;
    }

    /**
     * Test for string validation
     */
    public function testValidateValueString(): void
    {
        $inputValue = '0';
        $expectedResult = true;
        self::assertEquals($expectedResult, $this->model->validateValue($inputValue));
    }

    /**
     * Test for integer validation
     */
    public function testValidateValueInteger(): void
    {
        $inputValue = 0;
        $expectedResult = ['"Test" is a required value.'];
        $result = $this->model->validateValue($inputValue);
        self::assertEquals($expectedResult, [(string)$result[0]]);
    }

    /**
     * Test without length validation
     */
    public function testWithoutLengthValidation(): void
    {
        $expectedResult = true;
        $defaultAttributeData = [
            'store_label' => 'Test',
            'attribute_code' => 'test',
            'is_required' => 1,
            'validate_rules' => ['min_text_length' => 0, 'max_text_length' => 0, 'input_validation' => 0],
        ];

        $defaultAttributeData['validate_rules']['min_text_length'] = 2;
        $this->model->setAttribute($this->createAttribute($defaultAttributeData));
        self::assertEquals($expectedResult, $this->model->validateValue('t'));

        $defaultAttributeData['validate_rules']['max_text_length'] = 3;
        $this->model->setAttribute($this->createAttribute($defaultAttributeData));
        self::assertEquals($expectedResult, $this->model->validateValue('test'));
    }

    /**
     * Test of alphanumeric validation.
     *
     * @param {String} $value - provided value
     * @param {Boolean|Array} $expectedResult - validation result
     * @return void
     * @throws LocalizedException
     * @dataProvider alphanumDataProvider
     */
    public function testAlphanumericValidation($value, $expectedResult): void
    {
        $defaultAttributeData = [
            'store_label' => 'Test',
            'attribute_code' => 'test',
            'is_required' => 1,
            'validate_rules' => [
                'min_text_length' => 0,
                'max_text_length' => 10,
                'input_validation' => 'alphanumeric'
            ],
        ];

        $this->model->setAttribute($this->createAttribute($defaultAttributeData));
        self::assertEquals($expectedResult, $this->model->validateValue($value));
    }

    /**
     * Provides possible input values.
     *
     * @return array
     */
    public function alphanumDataProvider(): array
    {
        return [
            ['QazWsx', true],
            ['QazWsx123', true],
            ['QazWsx 123',
                [\Zend_Validate_Alnum::NOT_ALNUM => '"Test" contains non-alphabetic or non-numeric characters.']
            ],
            ['QazWsx_123',
                [\Zend_Validate_Alnum::NOT_ALNUM => '"Test" contains non-alphabetic or non-numeric characters.']
            ],
            ['QazWsx12345', [
                __('"%1" length must be equal or less than %2 characters.', 'Test', 10)]
            ],
        ];
    }

    /**
     * Test of alphanumeric validation with spaces.
     *
     * @param {String} $value - provided value
     * @param {Boolean|Array} $expectedResult - validation result
     * @return void
     * @throws LocalizedException
     * @dataProvider alphanumWithSpacesDataProvider
     */
    public function testAlphanumericValidationWithSpaces($value, $expectedResult): void
    {
        $defaultAttributeData = [
            'store_label' => 'Test',
            'attribute_code' => 'test',
            'is_required' => 1,
            'validate_rules' => [
                'min_text_length' => 0,
                'max_text_length' => 10,
                'input_validation' => 'alphanum-with-spaces'
            ],
        ];

        $this->model->setAttribute($this->createAttribute($defaultAttributeData));
        self::assertEquals($expectedResult, $this->model->validateValue($value));
    }

    /**
     * Provides possible input values.
     *
     * @return array
     */
    public function alphanumWithSpacesDataProvider(): array
    {
        return [
            ['QazWsx', true],
            ['QazWsx123', true],
            ['QazWsx 123', true],
            ['QazWsx_123',
                [\Zend_Validate_Alnum::NOT_ALNUM => '"Test" contains non-alphabetic or non-numeric characters.']
            ],
            ['QazWsx12345', [
                __('"%1" length must be equal or less than %2 characters.', 'Test', 10)]
            ],
        ];
    }

    /**
     * @param array $attributeData
     * @return \Magento\Eav\Model\Attribute
     */
    protected function createAttribute($attributeData): \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
    {
        $attributeClass = \Magento\Eav\Model\Attribute::class;
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eavTypeFactory = $this->createMock(\Magento\Eav\Model\Entity\TypeFactory::class);
        $arguments = $objectManagerHelper->getConstructArguments(
            $attributeClass,
            ['eavTypeFactory' => $eavTypeFactory, 'data' => $attributeData]
        );

        /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|
         * \PHPUnit_Framework_MockObject_MockObject
         */
        $attribute = $this->getMockBuilder($attributeClass)
            ->setMethods(['_init'])
            ->setConstructorArgs($arguments)
            ->getMock();
        return $attribute;
    }
}
