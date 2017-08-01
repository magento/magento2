<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper\Session;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;

/**
 * Class CurrentCustomerAddress
 * @since 2.0.0
 */
class CurrentCustomerAddress
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     * @since 2.0.0
     */
    protected $currentCustomer;

    /**
     * @var AccountManagementInterface
     * @since 2.0.0
     */
    protected $accountManagement;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param AccountManagementInterface $accountManagement
     * @since 2.0.0
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        AccountManagementInterface $accountManagement
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->accountManagement = $accountManagement;
    }

    /**
     * Returns default billing address form current customer
     *
     * @return AddressInterface|null
     * @since 2.0.0
     */
    public function getDefaultBillingAddress()
    {
        return $this->accountManagement->getDefaultBillingAddress($this->currentCustomer->getCustomerId());
    }

    /**
     * Returns default shipping address for current customer
     *
     * @return AddressInterface|null
     * @since 2.0.0
     */
    public function getDefaultShippingAddress()
    {
        return $this->accountManagement->getDefaultShippingAddress(
            $this->currentCustomer->getCustomerId()
        );
    }
}
