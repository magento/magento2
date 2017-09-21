<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model\Config\Source\Cart;

use \Magento\Checkout\Model\Config\Source\Cart\Summary;

class SummaryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Summary
     */
    private $model;

    protected function setUp()
    {
        $this->model = new Summary();
    }

    public function testToOptionArray()
    {
        $expectedResult = [
            ['value' => 0, 'label' => __('Display number of items in cart')],
            ['value' => 1, 'label' => __('Display item quantities')],
        ];
        $this->assertEquals($expectedResult, $this->model->toOptionArray());
    }
}
