<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Form\FormKey;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formKeyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_formKeyMock = $this->getMock(
            '\Magento\Framework\Data\Form\FormKey',
            ['getFormKey'],
            [],
            '',
            false
        );
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->_model = new \Magento\Framework\Data\Form\FormKey\Validator($this->_formKeyMock);
    }

    /**
     * @param string $formKey
     * @param bool $expected
     * @dataProvider validateDataProvider
     */
    public function testValidate($formKey, $expected)
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'form_key',
            null
        )->will(
            $this->returnValue($formKey)
        );
        $this->_formKeyMock->expects($this->once())->method('getFormKey')->will($this->returnValue('formKey'));
        $this->assertEquals($expected, $this->_model->validate($this->_requestMock));
    }

    public function validateDataProvider()
    {
        return [
            'formKeyExist' => ['formKey', true],
            'formKeyNotEqualToFormKeyInSession' => ['formKeySession', false]
        ];
    }
}
