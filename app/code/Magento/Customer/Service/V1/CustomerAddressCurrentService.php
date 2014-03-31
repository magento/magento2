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
 * Class CustomerAddressCurrentService
 */
class CustomerAddressCurrentService implements \Magento\Customer\Service\V1\CustomerAddressCurrentServiceInterface
{
    /**
     * @var CustomerCurrentService
     */
    protected $customerCurrentService;

    /**
     * @var CustomerAddressService
     */
    protected $customerAddressService;

    /**
     * @param CustomerCurrentService $customerCurrentService
     * @param CustomerAddressService $customerAddressService
     */
    public function __construct(
        \Magento\Customer\Service\V1\CustomerCurrentService $customerCurrentService,
        \Magento\Customer\Service\V1\CustomerAddressService $customerAddressService
    ) {
        $this->customerCurrentService = $customerCurrentService;
        $this->customerAddressService = $customerAddressService;
    }

    /**
     * Returns all addresses for current customer
     *
     * @return array|Data\Address[]
     */
    public function getCustomerAddresses()
    {
        return $this->customerAddressService->getAddresses($this->customerCurrentService->getCustomerId());
    }

    /**
     * Returns default billing address form current customer
     *
     * @return Data\Address|null
     */
    public function getDefaultBillingAddress()
    {
        return $this->customerAddressService->getDefaultBillingAddress($this->customerCurrentService->getCustomerId());
    }

    /**
     * Returns default shipping address for current customer
     *
     * @return Data\Address|null
     */
    public function getDefaultShippingAddress()
    {
        return $this->customerAddressService->getDefaultShippingAddress(
            $this->customerCurrentService->getCustomerId()
        );
    }
}
