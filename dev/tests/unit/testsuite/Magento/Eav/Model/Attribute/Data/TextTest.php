<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute\Data;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\Text
     */
    protected $_model;

    protected function setUp()
    {
        $locale = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            [],
            [],
            '',
            false,
            false
        );
        $localeResolver = $this->getMock(
            'Magento\Framework\Locale\ResolverInterface',
            [],
            [],
            '',
            false,
            false
        );
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $helper = $this->getMock('Magento\Framework\Stdlib\String', [], [], '', false, false);

        $attributeData = [
            'store_label' => 'Test',
            'attribute_code' => 'test',
            'is_required' => 1,
            'validate_rules' => ['min_text_length' => 0, 'max_text_length' => 0, 'input_validation' => 0],
        ];

        $attributeClass = 'Magento\Eav\Model\Entity\Attribute\AbstractAttribute';
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eavTypeFactory = $this->getMock('Magento\Eav\Model\Entity\TypeFactory', [], [], '', false, false);
        $arguments = $objectManagerHelper->getConstructArguments(
            $attributeClass,
            ['eavTypeFactory' => $eavTypeFactory, 'data' => $attributeData]
        );

        /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|
         * \PHPUnit_Framework_MockObject_MockObject
         */
        $attribute = $this->getMock($attributeClass, ['_init'], $arguments);
        $this->_model = new \Magento\Eav\Model\Attribute\Data\Text($locale, $logger, $localeResolver, $helper);
        $this->_model->setAttribute($attribute);
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
}
