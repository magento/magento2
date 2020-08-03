<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\FormKey;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Form\FormKey\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_formKeyMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    protected function setUp(): void
    {
        $this->_formKeyMock = $this->createPartialMock(FormKey::class, ['getFormKey']);
        $this->_requestMock = $this->createMock(Http::class);
        $this->_model = new Validator($this->_formKeyMock);
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
