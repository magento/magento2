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
namespace Magento\Framework\Pricing\Amount;

/**
 * Class BaseTest
 *
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getValue() without exclude argument
     */
    public function testGetValueWithoutExclude()
    {
        $amount = 1;
        $adjustments = [];
        $exclude = null;

        $model = $this->createEntity($amount, $adjustments);
        $result = $model->getValue($exclude);
        $this->assertEquals($amount, $result);
    }

    /**
     * Test getValue() with exclude argument
     */
    public function testGetValueWithExclude()
    {
        $amount = 1;
        $code = 'test_adjustment';
        $adjust = 5;
        $adjustments = [$code => $adjust];
        $expected = $amount - $adjust;

        $model = $this->createEntity($amount, $adjustments);
        $result = $model->getValue($code);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test __toString()
     */
    public function testToString()
    {
        $amount = 1;
        $adjustments = [];

        $model = $this->createEntity($amount, $adjustments);
        $result = (string)$model;
        $this->assertEquals($amount, $result);
    }

    /**
     * Test getBaseAmount()
     */
    public function testGetBaseAmount()
    {
        $amount = 1;
        $adjustments = [];

        $model = $this->createEntity($amount, $adjustments);
        $result = $model->getBaseAmount();
        $this->assertEquals($amount, $result);
    }

    /**
     * Test getAdjustmentAmount() if no adjustment amounts
     */
    public function testGetAdjustmentAmountNoAdjustments()
    {
        $amount = 1;
        $adjustments = [];

        $model = $this->createEntity($amount, $adjustments);
        $result = $model->getAdjustmentAmount('some_code');
        $this->assertFalse($result);
    }

    /**
     * Test getAdjustmentAmount() if adjustment amount exists
     */
    public function testGetAdjustmentAmountWithAdjustments()
    {
        $amount = 1;
        $code = 'test_code';
        $adjust = 10;
        $adjustments = [$code => $adjust];

        $model = $this->createEntity($amount, $adjustments);
        $result = $model->getAdjustmentAmount($code);
        $this->assertEquals($adjust, $result);
    }

    /**
     * Test getTotalAdjustmentAmount()
     */
    public function testGetTotalAdjustmentAmount()
    {
        $amount = 1;
        $adjust1 = 10;
        $adjust2 = 5;
        $expected = $adjust1 + $adjust2;
        $adjustments = [
            'test_code1' => $adjust1,
            'test_code2' => $adjust2
        ];

        $model = $this->createEntity($amount, $adjustments);
        $result = $model->getTotalAdjustmentAmount();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getAdjustmentAmounts()
     */
    public function testGetAdjustmentAmounts()
    {
        $amount = 1;
        $adjust = 5;
        $adjustments = [
            'test_code1' => $adjust,
        ];

        $model = $this->createEntity($amount, $adjustments);
        $result = $model->getAdjustmentAmounts();
        $this->assertEquals($adjustments, $result);
    }

    /**
     * Test getAdjustmentAmounts()
     */
    public function testHasAdjustment()
    {
        $amount = 1;
        $adjust = 5;
        $code = 'test_code1';
        $adjustments = [
            $code => $adjust,
        ];

        $model = $this->createEntity($amount, $adjustments);
        $result = $model->hasAdjustment($code);
        $this->assertTrue($result);
    }

    /**
     * Return instance of tested model
     *
     * @param string $amount
     * @param array $adjustmentAmounts
     * @return Base
     */
    protected function createEntity($amount, array $adjustmentAmounts = [])
    {
        return new \Magento\Framework\Pricing\Amount\Base($amount, $adjustmentAmounts);
    }
}
