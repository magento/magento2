<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CheckoutAgreements\Model;

class AgreementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\Agreement
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject('Magento\CheckoutAgreements\Model\Agreement');
    }

    /**
     * @covers \Magento\CheckoutAgreements\Model\Agreement::validateData
     *
     * @dataProvider validateDataDataProvider
     * @param \Magento\Framework\Object $inputData
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
                'inputData' => (new \Magento\Framework\Object())->setContentHeight('1px'),
                'expectedResult' => true,
            ],
            [
                'inputData' => (new \Magento\Framework\Object())->setContentHeight('1.1px'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new \Magento\Framework\Object())->setContentHeight('0.1in'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new \Magento\Framework\Object())->setContentHeight('5%'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new \Magento\Framework\Object())->setContentHeight('5'),
                'expectedResult' => true
            ],
            [
                'inputData' => (new \Magento\Framework\Object())->setContentHeight('px'),
                'expectedResult' => [
                    "Please input a valid CSS-height. For example 100px or 77pt or 20em or .5ex or 50%.",
                ]
            ],
            [
                'inputData' => (new \Magento\Framework\Object())->setContentHeight('abracadabra'),
                'expectedResult' => [
                    "Please input a valid CSS-height. For example 100px or 77pt or 20em or .5ex or 50%.",
                ]
            ],
        ];
    }
}
