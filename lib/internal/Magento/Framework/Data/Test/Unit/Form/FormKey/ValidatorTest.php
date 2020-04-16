<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Form\FormKey;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_formKeyMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    protected function setUp(): void
    {
        $this->_formKeyMock = $this->createPartialMock(\Magento\Framework\Data\Form\FormKey::class, ['getFormKey']);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
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
        )->willReturn(
            $formKey
        );
        $this->_formKeyMock->expects($this->once())->method('getFormKey')->willReturn('formKey');
        $this->assertEquals($expected, $this->_model->validate($this->_requestMock));
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'formKeyExist' => ['formKey', true],
            'formKeyNotEqualToFormKeyInSession' => ['formKeySession', false]
        ];
    }
}
