<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for \Magento\Eav\Model\Form
 */
namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Model\Form;
use Magento\Eav\Model\Validator\Attribute\Data;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @var Form
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
     * @var DataObject
     */
    protected $_entity = null;

    /**
     * Initialize form
     */
    protected function setUp(): void
    {
        $this->_model = $this->getMockBuilder(
            Form::class
        )->onlyMethods(
            ['_getFilteredFormAttributeCollection', '_getValidator', 'getEntity']
        )->disableOriginalConstructor()
            ->getMock();

        $this->_userAttribute = new DataObject(
            ['is_user_defined' => true, 'attribute_code' => 'attribute_visible_user', 'is_visible' => true]
        );
        $this->_systemAttribute = new DataObject(
            ['is_user_defined' => false, 'attribute_code' => 'attribute_invisible_system', 'is_visible' => false]
        );
        $this->_attributes = [$this->_userAttribute, $this->_systemAttribute];
        $this->_model->expects(
            $this->any()
        )->method(
            '_getFilteredFormAttributeCollection'
        )->willReturn(
            $this->_attributes
        );

        $this->_entity = new DataObject(['id' => 1, 'attribute_visible_user' => 'abc']);
        $this->_model->expects($this->any())->method('getEntity')->willReturn($this->_entity);
    }

    /**
     * Unset form
     */
    protected function tearDown(): void
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
            Data::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['isValid', 'getMessages']
            )->getMock();
        $validator->expects($this->once())->method('isValid')->willReturn($isValid);
        if ($messages) {
            $validator->expects($this->once())->method('getMessages')->willReturn($messages);
        } else {
            $validator->expects($this->never())->method('getMessages');
        }

        $this->_model->expects($this->once())->method('_getValidator')->willReturn($validator);

        $data = ['test' => true];
        $this->assertEquals($expected, $this->_model->validateData($data));
    }

    /**
     * Data provider for testValidateDataPassed
     *
     * @return array
     */
    public static function validateDataProvider()
    {
        return [
            'is_valid' => [true, true, null],
            'is_invalid' => [false, ['Invalid'], ['attribute_visible_user' => ['Invalid']]]
        ];
    }
}
