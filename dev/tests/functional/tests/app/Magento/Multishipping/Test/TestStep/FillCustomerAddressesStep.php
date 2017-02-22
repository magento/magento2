<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\TestStep;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\ObjectManager;
use Magento\Multishipping\Test\Page\MultishippingCheckoutAddresses;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Fill customer addresses form and proceed to next step.
 */
class FillCustomerAddressesStep implements TestStepInterface
{
    /**
     * Multishipping checkout addresses selection page.
     *
     * @var MultishippingCheckoutAddresses
     */
    protected $addresses;

    /**
     * Address renderer to get one line representation of shipping address.
     *
     * @var \Magento\Customer\Test\Block\Address\Renderer
     */
    protected $addressRender;

    /**
     * Responsible for instantiating objects.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Array of products in cart.
     *
     * @var array
     */
    protected $products;

    /**
     * @param MultishippingCheckoutAddresses $addresses
     * @param Customer $customer
     * @param ObjectManager $objectManager
     * @param array $products
     */
    public function __construct(
        MultishippingCheckoutAddresses $addresses,
        Customer $customer,
        ObjectManager $objectManager,
        $products
    ) {
        $this->addresses = $addresses;
        $this->customer = $customer;
        $this->products = $products;
        $this->objectManeger = $objectManager;
        $this->objectManeger->configure(
            ['\Magento\Customer\Test\Block\Address\Renderer' => ['shared' => false]]
        );
    }

    /**
     * Fill customer addresses and proceed to next step.
     *
     * @return void
     */
    public function run()
    {
        $addresses = $this->customer->getDataFieldConfig('address')['source']->getAddresses();
        $bindings = [];

        foreach ($this->products as $key => $product) {
            $productName = $product->getName();
            $addressRender = $this->objectManeger->create(
                '\Magento\Customer\Test\Block\Address\Renderer',
                ['address' => $addresses[$key], 'type' => 'oneline']
            );
            $bindings[$productName] = $addressRender->render();
        }
        $this->addresses->getAddressesBlock()->selectAddresses($bindings);
    }
}
