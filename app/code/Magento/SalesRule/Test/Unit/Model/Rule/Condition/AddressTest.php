<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition;

use Magento\SalesRule\Model\Rule\Condition\Address;
use PHPUnit\Framework\TestCase;

/**
 * Test for address rule condition
 */
class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $context = $this->createMock(\Magento\Rule\Model\Condition\Context::class);
        $directoryCountry = $this->createMock(\Magento\Directory\Model\Config\Source\Country::class);
        $directoryAllregion = $this->createMock(\Magento\Directory\Model\Config\Source\Allregion::class);
        $shippingAllmethods = $this->createMock(\Magento\Shipping\Model\Config\Source\Allmethods::class);
        $paymentAllmethods = $this->createMock(\Magento\Payment\Model\Config\Source\Allmethods::class);
        $this->model = new Address(
            $context,
            $directoryCountry,
            $directoryAllregion,
            $shippingAllmethods,
            $paymentAllmethods
        );
    }

    /**
     * Test that all attributes are present in options list
     */
    public function testLoadAttributeOptions(): void
    {
        $attributes = [
            'base_subtotal_with_discount',
            'base_subtotal_total_incl_tax',
            'base_subtotal',
            'total_qty',
            'weight',
            'payment_method',
            'shipping_method',
            'postcode',
            'region',
            'region_id',
            'country_id',
        ];

        $this->model->loadAttributeOptions();
        $this->assertEquals($attributes, array_keys($this->model->getAttributeOption()));
    }
}
