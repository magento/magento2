<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->model = $this->objectManager->getObject('\Magento\CheckoutAgreements\Model\Agreement');
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
                'expectedResult' => true
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
                    "Please input a valid CSS-height. For example 100px or 77pt or 20em or .5ex or 50%."
                ]
            ],
            [
                'inputData' => (new \Magento\Framework\Object())->setContentHeight('abracadabra'),
                'expectedResult' => [
                    "Please input a valid CSS-height. For example 100px or 77pt or 20em or .5ex or 50%."
                ]
            ],
        ];
    }
}
