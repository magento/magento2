<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\ObjectManager;

/**
 * Fill shipping address step.
 */
class FillShippingAddressStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Address fixture.
     *
     * @var Address
     */
    private $shippingAddress;

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    private $customer;

    /**
     * Customer shipping address data for select.
     *
     * @var array
     */
    private $shippingAddressCustomer;

    /**
     * Object manager instance.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param Customer $customer
     * @param ObjectManager $objectManager
     * @param FixtureFactory $fixtureFactory
     * @param Address|null $shippingAddress
     * @param array|null $shippingAddressCustomer
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        Customer $customer,
        ObjectManager $objectManager,
        FixtureFactory $fixtureFactory,
        Address $shippingAddress = null,
        $shippingAddressCustomer = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->customer = $customer;
        $this->objectManager = $objectManager;
        $this->fixtureFactory = $fixtureFactory;
        $this->shippingAddress = $shippingAddress;
        $this->shippingAddressCustomer = $shippingAddressCustomer;
    }

    /**
     * Fill shipping address.
     *
     * @return array
     */
    public function run()
    {
        $shippingAddress = null;
        if ($this->shippingAddress) {
            $shippingBlock = $this->checkoutOnepage->getShippingBlock();
            if ($shippingBlock->isPopupNewAddressButtonVisible()) {
                $shippingBlock->clickPopupNewAddressButton();
                $this->checkoutOnepage->getShippingAddressPopupBlock()
                    ->fill($this->shippingAddress)
                    ->clickSaveAddressButton();
            } else {
                $shippingBlock->fill($this->shippingAddress);
            }
            $shippingAddress = $this->shippingAddress;
        }
        if (isset($this->shippingAddressCustomer['new'])) {
            $shippingAddress = $this->fixtureFactory->create(
                'address',
                ['dataset' => $this->shippingAddressCustomer['new']]
            );
            $this->checkoutOnepage->getShippingBlock()->clickPopupNewAddressButton();
            $this->checkoutOnepage->getShippingAddressPopupBlock()->fill($shippingAddress)->clickSaveAddressButton();
        }
        if (isset($this->shippingAddressCustomer['added'])) {
            $addressIndex = $this->shippingAddressCustomer['added'];
            $shippingAddress = $this->customer->getDataFieldConfig('address')['source']->getAddresses()[$addressIndex];
            $address = $this->objectManager->create(
                \Magento\Customer\Test\Block\Address\Renderer::class,
                ['address' => $shippingAddress, 'type' => 'html_without_company']
            )->render();
            $shippingBlock = $this->checkoutOnepage->getShippingBlock();
            $shippingBlock->selectAddress($address);
        }

        return [
            'shippingAddress' => $shippingAddress,
        ];
    }
}
