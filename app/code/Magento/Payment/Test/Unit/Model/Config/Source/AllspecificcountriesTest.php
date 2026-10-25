<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Config\Source;

use Magento\Payment\Model\Config\Source\Allspecificcountries;
use PHPUnit\Framework\TestCase;

class AllspecificcountriesTest extends TestCase
{
    public function testToOptionArray()
    {
        $expectedArray = [
            ['value' => 0, 'label' => __('All Allowed Countries')],
            ['value' => 1, 'label' => __('Specific Countries')],
        ];
        $model = new Allspecificcountries();
        $this->assertEquals($expectedArray, $model->toOptionArray());
    }
}
