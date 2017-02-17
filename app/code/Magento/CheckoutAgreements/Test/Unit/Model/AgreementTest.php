<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

class AgreementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\Agreement
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(\Magento\CheckoutAgreements\Model\Agreement::class);
    }

    /**
     * @covers \Magento\CheckoutAgreements\Model\Agreement::validateData
     *
     * @dataProvider validateDataDataProvider
     * @param \Magento\Framework\DataObject $inputData
     * @param array|bool $expectedResult
     */
    public function testValidateData($inputData, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->model->validateData($inputData));
    }

    /**
     * @return array
     */
    public function validateDataDataProvider()
    {
        return [
            [
                'inputData' => (new \Magento\Framework\DataObject())->setContentHeight('1px'),
                'expectedResult' => true,
            ],
            [
                'inputData' => (new \Magento\Framework\DataObject())->setContentHeight('1.1px'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new \Magento\Framework\DataObject())->setContentHeight('0.1in'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new \Magento\Framework\DataObject())->setContentHeight('5%'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new \Magento\Framework\DataObject())->setContentHeight('5'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new \Magento\Framework\DataObject())->setContentHeight('px'),
                'expectedResult' => [
                    "Please input a valid CSS-height. For example 100px or 77pt or 20em or .5ex or 50%.",
                ]
            ],
            [
                'inputData' => (new \Magento\Framework\DataObject())->setContentHeight('abracadabra'),
                'expectedResult' => [
                    "Please input a valid CSS-height. For example 100px or 77pt or 20em or .5ex or 50%.",
                ]
            ],
        ];
    }
}
