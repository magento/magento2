<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Email\Test\Unit\ViewModel\Template\Preview;

use Magento\Email\ViewModel\Template\Preview\Form;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class FormTest
 *
 * @covers \Magento\Email\ViewModel\Template\Preview\Form
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /** @var Form */
    protected $form;

    /** @var  Http|\PHPUnit_Framework_MockObject_MockObject  */
    protected $requestMock;

    protected function setUp()
    {
        $this->requestMock = $this->createPartialMock(
            Http::class,
            ['getParam', 'getMethod']
        );

        $objectManagerHelper = new ObjectManager($this);

        $this->form = $objectManagerHelper->getObject(
            Form::class,
            ['request'=> $this->requestMock]
        );
    }

    /**
     * Tests that the form is created with the expected fields based on the request type.
     *
     * @dataProvider getFormFieldsDataProvider
     * @param string $httpMethod
     * @param array $httpParams
     * @param array $expectedFields
     * @throws LocalizedException
     */
    public function testGetFormFields(string $httpMethod, array $httpParams, array $expectedFields)
    {
        $this->requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($httpMethod);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap($httpParams);

        $actualFields = $this->form->getFormFields();

        $this->assertEquals($expectedFields, $actualFields);
    }

    /**
     * Tests that an exception is thrown when a required parameter is missing for the request type.
     *
     * @dataProvider getFormFieldsInvalidDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Missing expected parameter
     * @param string $httpMethod
     * @param array $httpParams
     */
    public function testGetFormFieldsMissingParameter(string $httpMethod, array $httpParams)
    {
        $this->requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($httpMethod);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturnMap($httpParams);

        $this->form->getFormFields();
    }

    /**
     * @return array
     */
    public function getFormFieldsDataProvider()
    {
        return [
            'get_request_valid' => [
                'httpMethod' => 'GET',
                'httpParams' => [
                    ['id', null, 1]
                ],
                'expectedFields' => [
                    'id' => 1
                ]
            ],
            'get_request_valid_ignore_params' => [
                'httpMethod' => 'GET',
                'httpParams' => [
                    ['id', null, 1],
                    ['text', null, 'Hello World'],
                    ['type', null, 2],
                    ['styles', null, '']
                ],
                'expectedFields' => [
                    'id' => 1
                ]
            ],
            'post_request_valid' => [
                'httpMethod' => 'POST',
                'httpParams' => [
                    ['text', null, 'Hello World'],
                    ['type', null, 2],
                    ['styles', null, '']
                ],
                'expectedFields' => [
                    'text' => 'Hello World',
                    'type' => 2,
                    'styles' => ''
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getFormFieldsInvalidDataProvider()
    {
        return [
            'get_request_missing_id' => [
                'httpMethod' => 'GET',
                'httpParams' => [
                    ['text', null, 'Hello World'],
                    ['type', null, 2],
                    ['styles', null, '']
                ]
            ],
            'post_request_missing_text' => [
                'httpMethod' => 'POST',
                'httpParams' => [
                    ['type', null, 2],
                    ['styles', null, '']
                ]
            ]
        ];
    }
}
