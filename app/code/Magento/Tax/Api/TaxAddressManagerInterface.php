<?php
/**
 * Created by PhpStorm.
 * User: serhii
 * Date: 25.08.17
 * Time: 15:48
 */
namespace Magento\Tax\Api;

use Magento\Customer\Model\Address;

/**
 * Interface to save data in customer session.
 */
interface TaxAddressManagerInterface
{
    /**
     * Set default Tax Billing and Shipping address into customer session after address save.
     *
     * @param Address $address
     * @return void
     */
    public function setDefaultAddressAfterSave(Address $address);

    /**
     * Set default Tax Shipping and Billing addresses into customer session after login.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface[] $addresses
     * @return void
     */
    public function setDefaultAddressAfterLogIn(array $addresses);
}