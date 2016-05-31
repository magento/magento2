<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\System\Config\Source;

use Magento\Paypal\Model\System\Config\Source\BmlPosition;

class BmlPositionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  BmlPosition */
    protected $model;

    protected function setUp()
    {
        $this->model = new BmlPosition();
    }

    public function testGetBmlPositionsHP()
    {
        $expectedResult = [
            '0' => __('Header (center)'),
            '1' => __('Sidebar (right)')
        ];
        $this->assertEquals($expectedResult, $this->model->getBmlPositionsHP());
    }

    public function testGetBmlPositionsCCP()
    {
        $expectedResult = [
            '0' => __('Header (center)'),
            '1' => __('Sidebar (right)')
        ];
        $this->assertEquals($expectedResult, $this->model->getBmlPositionsCCP());
    }

    public function testGetBmlPositionsCPP()
    {
        $expectedResult = [
            '0' => __('Header (center)'),
            '1' => __('Near PayPal Credit checkout button')
        ];
        $this->assertEquals($expectedResult, $this->model->getBmlPositionsCPP());
    }

    public function testGetBmlPositionsCheckout()
    {
        $expectedResult = [
            '0' => __('Header (center)'),
            '1' => __('Near PayPal Credit checkout button')
        ];
        $this->assertEquals($expectedResult, $this->model->getBmlPositionsCheckout());
    }
}
