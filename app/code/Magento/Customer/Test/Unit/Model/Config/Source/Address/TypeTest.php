<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Config\Source\Address;

class TypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Config\Source\Address\Type
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new \Magento\Customer\Model\Config\Source\Address\Type();
    }

    public function testToOptionArray()
    {
        $expected = ['billing' => 'Billing Address','shipping' => 'Shipping Address'];
        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
