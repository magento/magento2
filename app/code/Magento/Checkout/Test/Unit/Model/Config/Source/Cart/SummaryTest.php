<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Config\Source\Cart;

use Magento\Checkout\Model\Config\Source\Cart\Summary;
use PHPUnit\Framework\TestCase;

class SummaryTest extends TestCase
{
    /**
     * @var Summary
     */
    private $model;

    protected function setUp(): void
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
