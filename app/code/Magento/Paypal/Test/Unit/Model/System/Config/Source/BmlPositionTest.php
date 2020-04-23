<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\System\Config\Source;

use Magento\Paypal\Model\System\Config\Source\BmlPosition;
use PHPUnit\Framework\TestCase;

class BmlPositionTest extends TestCase
{
    /** @var  BmlPosition */
    protected $model;

    protected function setUp(): void
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
