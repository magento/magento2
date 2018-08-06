<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\Attribute\Data;

class TextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\Text
     */
    protected $_model;

    protected function setUp()
    {
        $locale = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $localeResolver = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $helper = $this->createMock(\Magento\Framework\Stdlib\StringUtils::class);

        $this->_model = new \Magento\Eav\Model\Attribute\Data\Text($locale, $logger, $localeResolver, $helper);
        $this->_model->setAttribute(
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

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testValidateValueString()
    {
        $inputValue = '0';
        $expectedResult = true;
        $this->assertEquals($expectedResult, $this->_model->validateValue($inputValue));
    }

    public function testValidateValueInteger()
    {
        $inputValue = 0;
        $expectedResult = ['"Test" is a required value.'];
        $result = $this->_model->validateValue($inputValue);
        $this->assertEquals($expectedResult, [(string)$result[0]]);
    }

    public function testWithoutLengthValidation()
    {
        $expectedResult = true;
        $defaultAttributeData = [
            'store_label' => 'Test',
            'attribute_code' => 'test',
            'is_required' => 1,
            'validate_rules' => ['min_text_length' => 0, 'max_text_length' => 0, 'input_validation' => 0],
        ];

        $defaultAttributeData['validate_rules']['min_text_length'] = 2;
        $this->_model->setAttribute($this->createAttribute($defaultAttributeData));
        $this->assertEquals($expectedResult, $this->_model->validateValue('t'));

        $defaultAttributeData['validate_rules']['max_text_length'] = 3;
        $this->_model->setAttribute($this->createAttribute($defaultAttributeData));
        $this->assertEquals($expectedResult, $this->_model->validateValue('test'));
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
