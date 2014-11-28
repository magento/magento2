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
