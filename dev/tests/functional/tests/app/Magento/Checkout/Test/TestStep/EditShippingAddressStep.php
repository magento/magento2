<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Edit shipping address on checkout step.
 */
class EditShippingAddressStep implements TestStepInterface
{
    /**
     * Object Manager.
     *
     * @var \Magento\Mtf\ObjectManager
     */
    private $objectManager;

    /**
     * Checkout One page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Shipping Address fixture.
     *
     * @var Address
     */
    private $address;

    /**
     * Shipping Address for edit fixture.
     *
     * @var Address
     */
    private $editAddress;

    /**
     * Save Shipping Address.
     *
     * @var boolean
     */
    private $save;

    /**
     * @constructor
     * @param ObjectManager $objectManager
     * @param CheckoutOnepage $checkoutOnepage
     * @param Address|null $editShippingAddress [optional]
     * @param Address|null $shippingAddress [optional]
     * @param boolean $editSave [optional]
     */
    public function __construct(
        ObjectManager $objectManager,
        CheckoutOnepage $checkoutOnepage,
        Address $editShippingAddress = null,
        Address $shippingAddress = null,
        $editSave = true
    ) {
        $this->objectManager = $objectManager;
        $this->checkoutOnepage = $checkoutOnepage;
        $this->editAddress = $editShippingAddress;
        $this->address = $shippingAddress;
        $this->save = $editSave;
    }

    /**
     * Create customer account.
     *
     * @return array
     */
    public function run()
    {
        $address = $this->objectManager->create(
            \Magento\Customer\Test\Block\Address\Renderer::class,
            ['address' => $this->address, 'type' => 'html_without_company']
        )->render();

        $shippingBlock = $this->checkoutOnepage->getShippingBlock();
        $shippingBlock->editAddress($address);
        if ($this->editAddress) {
            $shippingBlock->getAddressModalBlock()->fill($this->editAddress);
        }
        if ($this->save) {
            $shippingBlock->getAddressModalBlock()->save();
        } else {
            $shippingBlock->getAddressModalBlock()->cancel();
        }

        return ['shippingAddress' => $this->address];
    }
}
