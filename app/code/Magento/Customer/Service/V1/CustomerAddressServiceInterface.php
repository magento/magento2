<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service\V1;

/**
 * Interface CustomerAddressServiceInterface
 */
interface CustomerAddressServiceInterface
{
    /**
     * Retrieve all Customer Addresses
     *
     * @param string $customerId
     * @return \Magento\Customer\Service\V1\Data\Address[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the customer Id is invalid
     */
    public function getAddresses($customerId);

    /**
     * Retrieve default billing address
     *
     * @param string $customerId
     * @return \Magento\Customer\Service\V1\Data\Address
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the customer Id is invalid
     */
    public function getDefaultBillingAddress($customerId);

    /**
     * Retrieve default shipping address
     *
     * @param string $customerId
     * @return \Magento\Customer\Service\V1\Data\Address
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the customer Id is invalid
     */
    public function getDefaultShippingAddress($customerId);

    /**
     * Retrieve address by id
     *
     * @param string $addressId
     * @return \Magento\Customer\Service\V1\Data\Address
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no address can be found for the provided id.
     */
    public function getAddress($addressId);

    /**
     * Removes an address by id.
     *
     * @param string $addressId
     * @return bool True if the address was deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no address can be found for the provided id.
     */
    public function deleteAddress($addressId);

    /**
     * Insert and/or update a list of addresses.
     *
     * This will add the addresses to the provided customerId.
     * Only one address can be the default billing or shipping
     * so if more than one is set, or if one was already set
     * then the last address in this provided list will take
     * over as the new default.
     *
     * This doesn't support partial updates to addresses, meaning
     * that a full set of data must be provided with each Address
     *
     * @param string $customerId
     * @param \Magento\Customer\Service\V1\Data\Address[] $addresses
     * @throws \Magento\Framework\Exception\InputException If there are validation errors.
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with customerId is not found.
     * @throws \Exception If there were issues during the save operation
     * @return int[] address ids
     */
    public function saveAddresses($customerId, $addresses);

    /**
     * Validate a list of addresses.
     *
     * @param \Magento\Customer\Service\V1\Data\Address[] $addresses
     * @return bool true All addresses passed validation.
     * @throws \Magento\Framework\Exception\InputException If there are validation errors.
     */
    public function validateAddresses($addresses);
}
