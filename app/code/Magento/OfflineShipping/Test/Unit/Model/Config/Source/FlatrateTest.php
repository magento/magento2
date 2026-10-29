<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\Config\Source;

use Magento\OfflineShipping\Model\Config\Source\Flatrate;
use PHPUnit\Framework\TestCase;

class FlatrateTest extends TestCase
{
    /**
     * @var Flatrate
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new Flatrate();
    }

    public function testToOptionArray()
    {
        $expected = [
            ['value' => '', 'label' => __('None')],
            ['value' => 'O', 'label' => __('Per Order')],
            ['value' => 'I', 'label' => __('Per Item')]
        ];

        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
