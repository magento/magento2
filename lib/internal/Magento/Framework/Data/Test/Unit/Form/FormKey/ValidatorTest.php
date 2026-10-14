<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\FormKey;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Form\FormKey\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
     * @param bool $expected     */
    #[DataProvider('validateDataProvider')]
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
    public static function validateDataProvider()
    {
        return [
            'formKeyExist' => ['formKey', true],
            'formKeyNotEqualToFormKeyInSession' => ['formKeySession', false]
        ];
    }

    /**
     * @param mixed $formKey
     */
    #[DataProvider('rejectedWithoutComparisonDataProvider')]
    public function testValidateRejectsNonStringOrEmptyFormKeyWithoutComparison($formKey): void
    {
        $this->_requestMock->expects($this->once())
            ->method('getParam')
            ->with('form_key', null)
            ->willReturn($formKey);
        $this->_formKeyMock->expects($this->never())->method('getFormKey');

        $this->assertFalse($this->_model->validate($this->_requestMock));
    }

    /**
     * @return array
     */
    public static function rejectedWithoutComparisonDataProvider(): array
    {
        return [
            'array' => [['formKey']],
            'emptyArray' => [[]],
            'null' => [null],
            'emptyString' => [''],
            'zeroString' => ['0'],
            'integer' => [123456],
        ];
    }

    public function testValidateComparesIntegerLikeStringAsString(): void
    {
        $this->_requestMock->expects($this->once())
            ->method('getParam')
            ->with('form_key', null)
            ->willReturn('123456');
        $this->_formKeyMock->expects($this->once())->method('getFormKey')->willReturn('123456');

        $this->assertTrue($this->_model->validate($this->_requestMock));
    }
}
