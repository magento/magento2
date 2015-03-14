<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote;

use \Magento\Quote\Model\Quote\Payment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payment
     */
    private $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            '\Magento\Quote\Model\Quote\Payment'
        );
    }

    /**
     * @param int|string|null $databaseValue
     * @param int|string|null $expectedValue
     * @dataProvider yearValueDataProvider
     */
    public function testGetCcExpYearReturnsValidValue($databaseValue, $expectedValue)
    {
        $this->model->setData('cc_exp_year', $databaseValue);
        $this->assertEquals($expectedValue, $this->model->getCcExpYear());
    }

    /**
     * @return array
     */
    public function yearValueDataProvider()
    {
        return [
            [null, null],
            [0, null],
            ['0', null],
            [1939, 1939],
        ];
    }
}
