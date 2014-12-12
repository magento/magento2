<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Helper\Session;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;

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
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param AccountManagementInterface $accountManagement
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
     */
    public function getDefaultBillingAddress()
    {
        return $this->accountManagement->getDefaultBillingAddress($this->currentCustomer->getCustomerId());
    }

    /**
     * Returns default shipping address for current customer
     *
     * @return AddressInterface|null
     */
    public function getDefaultShippingAddress()
    {
        return $this->accountManagement->getDefaultShippingAddress(
            $this->currentCustomer->getCustomerId()
        );
    }
}
