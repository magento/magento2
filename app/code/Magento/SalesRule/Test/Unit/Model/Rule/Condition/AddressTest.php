<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class AddressTest
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\SalesRule\Model\Rule\Condition\Address */
    protected $address;

    public function testLoadAttributeOptions()
    {
        $attributes = [
            'base_subtotal' => __('Subtotal'),
            'total_qty' => __('Total Items Quantity'),
            'weight' => __('Total Weight'),
            'shipping_method' => __('Shipping Method'),
            'postcode' => __('Shipping Postcode'),
            'region' => __('Shipping Region'),
            'region_id' => __('Shipping State/Province'),
            'country_id' => __('Shipping Country'),
        ];

        $objectManager = new ObjectManager($this);

        $this->address = $objectManager->getObject(
            \Magento\SalesRule\Model\Rule\Condition\Address::class
        );
        $this->address->loadAttributeOptions();

        $this->assertEquals($attributes, $this->address->getAttributeOption());
    }
}
