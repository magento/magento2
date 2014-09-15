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

namespace Magento\Customer\Model;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\StoreManagerInterface;

/**
 * Registry for \Magento\Customer\Model\Customer
 */
class CustomerRegistry
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var array
     */
    private $customerRegistryById = [];

    /**
     * @var array
     */
    private $customerRegistryByEmail = [];

    const REGISTRY_SEPARATOR = ':';

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(CustomerFactory $customerFactory, StoreManagerInterface $storeManager)
    {
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve Customer Model from registry given an id
     *
     * @param string $customerId
     * @return Customer
     * @throws NoSuchEntityException
     */
    public function retrieve($customerId)
    {
        if (isset($this->customerRegistryById[$customerId])) {
            return $this->customerRegistryById[$customerId];
        }
        /** @var Customer $customer */
        $customer = $this->customerFactory->create()->load($customerId);
        if (!$customer->getId()) {
            // customer does not exist
            throw NoSuchEntityException::singleField('customerId', $customerId);
        } else {
            $emailKey = $this->getEmailKey($customer->getEmail(), $customer->getWebsiteId());
            $this->customerRegistryById[$customerId] = $customer;
            $this->customerRegistryByEmail[$emailKey] = $customer;
            return $customer;
        }
    }

    /**
     * Retrieve Customer Model from registry given an email
     *
     * @param string $customerEmail Customers email address
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return Customer
     * @throws NoSuchEntityException
     */
    public function retrieveByEmail($customerEmail, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $emailKey = $this->getEmailKey($customerEmail, $websiteId);
        if (isset($this->customerRegistryByEmail[$emailKey])) {
            return $this->customerRegistryByEmail[$emailKey];
        }

        /** @var Customer $customer */
        $customer = $this->customerFactory->create();

        if (isset($websiteId)) {
            $customer->setWebsiteId($websiteId);
        }

        $customer->loadByEmail($customerEmail);
        if (!$customer->getEmail()) {
            // customer does not exist
            throw new NoSuchEntityException(
                NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                [
                    'fieldName' => 'email',
                    'fieldValue' => $customerEmail,
                    'field2Name' => 'websiteId',
                    'field2Value' => $websiteId,
                ]
            );
        } else {
            $this->customerRegistryById[$customer->getId()] = $customer;
            $this->customerRegistryByEmail[$emailKey] = $customer;
            return $customer;
        }
    }

    /**
     * Remove instance of the Customer Model from registry given an id
     *
     * @param int $customerId
     * @return void
     */
    public function remove($customerId)
    {
        if (isset($this->customerRegistryById[$customerId])) {
            /** @var Customer $customer */
            $customer = $this->customerRegistryById[$customerId];
            $emailKey = $this->getEmailKey($customer->getEmail(), $customer->getWebsiteId());
            unset($this->customerRegistryByEmail[$emailKey]);
            unset($this->customerRegistryById[$customerId]);
        }
    }

    /**
     * Remove instance of the Customer Model from registry given an email
     *
     * @param string $customerEmail Customers email address
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return void
     */
    public function removeByEmail($customerEmail, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $emailKey = $this->getEmailKey($customerEmail, $websiteId);
        if ($emailKey) {
            /** @var Customer $customer */
            $customer = $this->customerRegistryByEmail[$emailKey];
            unset($this->customerRegistryByEmail[$emailKey]);
            unset($this->customerRegistryById[$customer->getId()]);
        }
    }

    /**
     * Create registry key
     *
     * @param string $customerEmail
     * @param string $websiteId
     * @return string
     */
    protected function getEmailKey($customerEmail, $websiteId)
    {
        return $customerEmail . self::REGISTRY_SEPARATOR . $websiteId;
    }
}
