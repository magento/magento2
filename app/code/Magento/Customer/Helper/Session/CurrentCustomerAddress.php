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
namespace Magento\Customer\Helper\Session;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface;
use Magento\Customer\Service\V1\Data\Address;

/**
 * Class CurrentCustomerAddress
 */
class CurrentCustomerAddress
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface
     */
    protected $customerAddressService;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param CustomerAddressServiceInterface $customerAddressService
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        CustomerAddressServiceInterface $customerAddressService
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customerAddressService = $customerAddressService;
    }

    /**
     * Returns all addresses for current customer
     *
     * @return Address[]
     */
    public function getCustomerAddresses()
    {
        return $this->customerAddressService->getAddresses($this->currentCustomer->getCustomerId());
    }

    /**
     * Returns default billing address form current customer
     *
     * @return Address|null
     */
    public function getDefaultBillingAddress()
    {
        return $this->customerAddressService->getDefaultBillingAddress($this->currentCustomer->getCustomerId());
    }

    /**
     * Returns default shipping address for current customer
     *
     * @return Address|null
     */
    public function getDefaultShippingAddress()
    {
        return $this->customerAddressService->getDefaultShippingAddress(
            $this->currentCustomer->getCustomerId()
        );
    }
}
