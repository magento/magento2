<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for \Magento\Eav\Model\Form
 */
namespace Magento\Eav\Test\Unit\Model;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Form
     */
    protected $_model = null;

    /**
     * @var array
     */
    protected $_attributes = null;

    /**
     * @var array
     */
    protected $_systemAttribute = null;

    /**
     * @var array
     */
    protected $_userAttribute = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_entity = null;

    /**
     * Initialize form
     */
    protected function setUp()
    {
        $this->_model = $this->getMockBuilder(
            'Magento\Eav\Model\Form'
        )->setMethods(
            ['_getFilteredFormAttributeCollection', '_getValidator', 'getEntity']
        )->disableOriginalConstructor()->getMock();

        $this->_userAttribute = new \Magento\Framework\DataObject(
            ['is_user_defined' => true, 'attribute_code' => 'attribute_visible_user', 'is_visible' => true]
        );
        $this->_systemAttribute = new \Magento\Framework\DataObject(
            ['is_user_defined' => false, 'attribute_code' => 'attribute_invisible_system', 'is_visible' => false]
        );
        $this->_attributes = [$this->_userAttribute, $this->_systemAttribute];
        $this->_model->expects(
            $this->any()
        )->method(
            '_getFilteredFormAttributeCollection'
        )->will(
            $this->returnValue($this->_attributes)
        );

        $this->_entity = new \Magento\Framework\DataObject(['id' => 1, 'attribute_visible_user' => 'abc']);
        $this->_model->expects($this->any())->method('getEntity')->will($this->returnValue($this->_entity));
    }

    /**
     * Unset form
     */
    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Test getAttributes
     */
    public function testGetAttributes()
    {
        $expected = [
            'attribute_visible_user' => $this->_userAttribute,
            'attribute_invisible_system' => $this->_systemAttribute,
        ];
        $this->assertEquals($expected, $this->_model->getAttributes());
    }

    /**
     * Test getUserAttributes
     */
    public function testGetUserAttributes()
    {
        $expected = ['attribute_visible_user' => $this->_userAttribute];
        $this->assertEquals($expected, $this->_model->getUserAttributes());
    }

    /**
     * Test getSystemAttributes
     */
    public function testGetSystemAttributes()
    {
        $expected = ['attribute_invisible_system' => $this->_systemAttribute];
        $this->assertEquals($expected, $this->_model->getSystemAttributes());
    }

    /**
     * Test getAllowedAttributes
     */
    public function testGetAllowedAttributes()
    {
        $expected = ['attribute_visible_user' => $this->_userAttribute];
        $this->assertEquals($expected, $this->_model->getAllowedAttributes());
    }

    /**
     * Test validateData method
     *
     * @dataProvider validateDataProvider
     *
     * @param bool $isValid
     * @param bool|array $expected
     * @param null|array $messages
     */
    public function testValidateDataPassed($isValid, $expected, $messages = null)
    {
        $validator = $this->getMockBuilder(
            'Magento\Eav\Model\Validator\Attribute\Data'
        )->disableOriginalConstructor()->setMethods(
            ['isValid', 'getMessages']
        )->getMock();
        $validator->expects($this->once())->method('isValid')->will($this->returnValue($isValid));
        if ($messages) {
            $validator->expects($this->once())->method('getMessages')->will($this->returnValue($messages));
        } else {
            $validator->expects($this->never())->method('getMessages');
        }

        $this->_model->expects($this->once())->method('_getValidator')->will($this->returnValue($validator));

        $data = ['test' => true];
        $this->assertEquals($expected, $this->_model->validateData($data));
    }

    /**
     * Data provider for testValidateDataPassed
     *
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'is_valid' => [true, true, null],
            'is_invalid' => [false, ['Invalid'], ['attribute_visible_user' => ['Invalid']]]
        ];
    }
}
