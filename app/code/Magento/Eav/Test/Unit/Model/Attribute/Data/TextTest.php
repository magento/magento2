<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\Text;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\Alnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Eav text attribute model test
 */
class TextTest extends TestCase
{
    /**
     * @var Text
     */
    private $model;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $locale = $this->getMockForAbstractClass(TimezoneInterface::class);
        $localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $helper = new StringUtils();

        $this->model = new Text($locale, $logger, $localeResolver, $helper);
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
    protected function tearDown(): void
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
                [Alnum::NOT_ALNUM => '"Test" contains non-alphabetic or non-numeric characters.']
            ],
            ['QazWsx_123',
                [Alnum::NOT_ALNUM => '"Test" contains non-alphabetic or non-numeric characters.']
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
                [Alnum::NOT_ALNUM => '"Test" contains non-alphabetic or non-numeric characters.']
            ],
            ['QazWsx12345', [
                __('"%1" length must be equal or less than %2 characters.', 'Test', 10)]
            ],
        ];
    }

    /**
     * Test for string with diacritics validation
     */
    public function testValidateValueStringWithDiacritics(): void
    {
        $inputValue = "á â à å ä ð é ê è ë í î ì ï ó ô ò ø õ ö ú û ù ü æ œ ç ß a ĝ ń ŕ ý ð ñ";
        $expectedResult = true;
        self::assertEquals($expectedResult, $this->model->validateValue($inputValue));
    }

    /**
     * @param array $attributeData
     * @return Attribute
     */
    protected function createAttribute($attributeData): AbstractAttribute
    {
        $attributeClass = Attribute::class;
        $objectManagerHelper = new ObjectManager($this);
        $eavTypeFactory = $this->createMock(TypeFactory::class);
        $arguments = $objectManagerHelper->getConstructArguments(
            $attributeClass,
            ['eavTypeFactory' => $eavTypeFactory, 'data' => $attributeData]
        );

        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|MockObject $attribute
         */
        $attribute = $this->getMockBuilder($attributeClass)
            ->setMethods(['_init'])
            ->setConstructorArgs($arguments)
            ->getMock();
        return $attribute;
    }
}
