<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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

    /**
     * Test that postcode with space matches postcode without space (e.g. Swedish format "100 00")
     *
     * @return void
     */
    public function testValidateAttributePostcodeWithSpaceMatchesWithoutSpace(): void
    {
        $this->model->setAttribute('postcode');
        $this->model->setOperator('==');
        $this->model->setValueParsed('100 00');

        $this->assertTrue(
            $this->model->validateAttribute('100 00'),
            'Postcode "100 00" should match rule value "100 00"'
        );
        $this->assertTrue(
            $this->model->validateAttribute('10000'),
            'Postcode "10000" should match rule value "100 00" (normalized)'
        );
    }

    /**
     * Test that postcode rule without space matches address with space
     *
     * @return void
     */
    public function testValidateAttributePostcodeRuleWithoutSpaceMatchesAddressWithSpace(): void
    {
        $this->model->setAttribute('postcode');
        $this->model->setOperator('==');
        $this->model->setValueParsed('10000');

        $this->assertTrue(
            $this->model->validateAttribute('100 00'),
            'Postcode "100 00" should match rule value "10000" (normalized)'
        );
    }
}
