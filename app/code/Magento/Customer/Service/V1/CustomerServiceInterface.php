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

use Magento\Exception\InputException;
use Magento\Exception\NoSuchEntityException;

/**
 * Manipulate Customer Address Entities *
 */
interface CustomerServiceInterface
{
    /**
     * Create or update customer information
     *
     * @param Dto\Customer $customer
     * @param string $password
     * @throws \Magento\Customer\Exception If something goes wrong during save
     * @throws InputException If bad input is provided
     * @return int customer ID
     */
    public function saveCustomer(Dto\Customer $customer, $password = null);

    /**
     * Retrieve Customer
     *
     * @param int $customerId
     * @throws NoSuchEntityException If customer with customerId is not found.
     * @return Dto\Customer
     */
    public function getCustomer($customerId);

    /**
     * Retrieve customer by his email.
     *
     * @param string $customerEmail
     * @param int|null $websiteId
     * @throws NoSuchEntityException If customer with the specified email is not found.
     * @return Dto\Customer
     */
    public function getCustomerByEmail($customerEmail, $websiteId = null);

    /**
     * Delete Customer
     *
     * @param int $customerId
     * @throws \Magento\Customer\Exception If something goes wrong during delete
     * @throws NoSuchEntityException If customer with customerId is not found.
     * @return void
     */
    public function deleteCustomer($customerId);
}
