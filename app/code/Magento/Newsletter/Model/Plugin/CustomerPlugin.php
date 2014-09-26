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
namespace Magento\Newsletter\Model\Plugin;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\Data\Customer;
use Magento\Customer\Service\V1\Data\CustomerDetails;
use Magento\Newsletter\Model\SubscriberFactory;

class CustomerPlugin
{
    /**
     * Factory used for manipulating newsletter subscriptions
     *
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * Constructor
     *
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        SubscriberFactory $subscriberFactory
    ) {
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Plugin after create customer that updates any newsletter subscription that may have existed.
     *
     * @param CustomerAccountServiceInterface $subject
     * @param Customer $customer
     * @return Customer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateCustomer(CustomerAccountServiceInterface $subject, Customer $customer)
    {
        $this->subscriberFactory->create()->updateSubscription($customer->getId());

        return $customer;
    }

    /**
     * Plugin around updating a customer account that updates any newsletter subscription that may have existed.
     *
     * @param CustomerAccountServiceInterface $subject
     * @param callable $updateCustomer
     * @param string $customerId
     * @param CustomerDetails $customerDetails
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundUpdateCustomer(
        CustomerAccountServiceInterface $subject,
        callable $updateCustomer,
        $customerId,
        CustomerDetails $customerDetails
    ) {
        $result = $updateCustomer($customerId, $customerDetails);

        $this->subscriberFactory->create()->updateSubscription($customerDetails->getCustomer()->getId());

        return $result;
    }

    /**
     * Plugin after delete customer that updates any newsletter subscription that may have existed.
     *
     * @param CustomerAccountServiceInterface $subject
     * @param callable $deleteCustomer Function we are wrapping around
     * @param int $customerId Input to the function
     * @return bool
     */
    public function aroundDeleteCustomer(
        CustomerAccountServiceInterface $subject,
        callable $deleteCustomer,
        $customerId
    ) {
        $customer = $subject->getCustomer($customerId);

        $result = $deleteCustomer($customerId);

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($customer->getEmail());
        if ($subscriber->getId()) {
            $subscriber->delete();
        }

        return $result;
    }
}
