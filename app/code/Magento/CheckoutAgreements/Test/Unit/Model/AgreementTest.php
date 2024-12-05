<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\Agreement;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class AgreementTest extends TestCase
{
    /**
     * @var Agreement
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Agreement::class);
    }

    /**
     * @covers \Magento\CheckoutAgreements\Model\Agreement::validateData
     *
     * @dataProvider validateDataDataProvider
     * @param DataObject $inputData
     * @param array|bool $expectedResult
     */
    public function testValidateData($inputData, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->model->validateData($inputData));
    }

    /**
     * @return array
     */
    public static function validateDataDataProvider()
    {
        return [
            [
                'inputData' => (new DataObject())->setContentHeight('1px'),
                'expectedResult' => true,
            ],
            [
                'inputData' => (new DataObject())->setContentHeight('1.1px'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new DataObject())->setContentHeight('0.1in'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new DataObject())->setContentHeight('5%'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new DataObject())->setContentHeight('5'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new DataObject())->setContentHeight('px'),
                'expectedResult' => [
                    "Please input a valid CSS-height. For example 100px or 77pt or 20em or .5ex or 50%.",
                ]
            ],
            [
                'inputData' => (new DataObject())->setContentHeight('abracadabra'),
                'expectedResult' => [
                    "Please input a valid CSS-height. For example 100px or 77pt or 20em or .5ex or 50%.",
                ]
            ],
        ];
    }
}
