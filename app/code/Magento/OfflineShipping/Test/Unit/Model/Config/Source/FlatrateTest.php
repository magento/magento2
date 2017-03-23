<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\Config\Source;

class FlatrateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Config\Source\Flatrate
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\OfflineShipping\Model\Config\Source\Flatrate();
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
