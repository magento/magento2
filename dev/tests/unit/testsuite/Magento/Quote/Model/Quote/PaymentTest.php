<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\TestFramework\Helper\ObjectManager;

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
        return array(
            array(null, null),
            array(0, null),
            array('0', null),
            array(1939, 1939),
        );
    }
}
