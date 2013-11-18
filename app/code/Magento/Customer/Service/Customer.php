<?php
/**
 * Customer service is responsible for customer business workflow encapsulation
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service;

class Customer
{
    /**
     * @var callable
     */
    protected $_beforeSaveCallback = null;

    /**
     * @var callable
     */
    protected $_afterSaveCallback = null;

    /**
     * @var \Magento\Customer\Helper\Data
     */
    protected $_dataHelper = null;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory = null;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_addressFactory = null;

    /**
     * @var bool
     */
    protected $_isAdminStore = true;

    /**
     * @var bool
     */
    protected $_sendRemainderEmail = true;
    // todo: drop the parameter as soon as front controller of webapi will be able to return routers

    /**
     * Constructor
     *
     * @param \Magento\Customer\Helper\Data $helper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param bool $isAdminStore
     */
    public function __construct(
        \Magento\Customer\Helper\Data $helper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        $isAdminStore = true
    ) {
        $this->_dataHelper = $helper;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_isAdminStore = $isAdminStore;
    }

    /**
     * Set is admin store flag.
     *
     * @param bool $isAdminStore
     * @return \Magento\Customer\Service\Customer
     */
    public function setIsAdminStore($isAdminStore)
    {
        $this->_isAdminStore = $isAdminStore;
        return $this;
    }

    /**
     * Set flag if send remainder email
     *
     * @param bool $flag
     * @return \Magento\Customer\Service\Customer
     */
    public function setSendRemainderEmail($flag)
    {
        // todo: drop next string as soon as front controller of webapi will be able to return routers
        $this->_sendRemainderEmail = (bool)$flag;
        return $this;
    }

    /**
     * Set before save callback.
     *
     * @param callable $callback
     * @return \Magento\Customer\Service\Customer
     */
    public function setBeforeSaveCallback($callback)
    {
        $this->_beforeSaveCallback = $callback;
        return $this;
    }

    /**
     * Set after save callback.
     *
     * @param callable $callback
     * @return \Magento\Customer\Service\Customer
     */
    public function setAfterSaveCallback($callback)
    {
        $this->_afterSaveCallback = $callback;
        return $this;
    }

    /**
     * Create customer with optional ability of adding addresses.
     *
     * @param array $customerData
     * @param array|null $addressesData array of addresses
     * @return \Magento\Customer\Model\Customer
     */
    public function create(array $customerData, array $addressesData = null)
    {
        $customer = $this->_customerFactory->create();
        $this->_preparePasswordForSave($customer, $customerData);
        $this->_save($customer, $customerData, $addressesData);

        return $customer;
    }

    /**
     * Update customer entity.
     *
     * Update customer with optional ability to update customer addresses.
     * Addresses that are not in $addressesData array but present in customer addresses collection will be removed.
     * New address is created in case when no entity_id is present, otherwise corresponding address will be updated
     * with data provided.
     *
     * @param string|int $customerId
     * @param array $customerData
     * @param array|null $addressesData array of addresses
     * @return \Magento\Customer\Model\Customer
     */
    public function update($customerId, array $customerData, array $addressesData = null)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->_loadCustomerById($customerId);

        $this->_save($customer, $customerData, $addressesData);
        if ($customerData) {
            $this->_changePassword($customer, $customerData);
        }

        return $customer;
    }

    /**
     * Save customer entity. Perform supplementary business workflow actions
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $customerData
     * @param array|null $addressesData
     * @return \Magento\Customer\Service\Customer
     */
    protected function _save($customer, array $customerData, array $addressesData = null)
    {
        if ($customerData) {
            $this->_setDataUsingMethods($customer, $customerData);
        }
        $this->_beforeSave($customer, $customerData, $addressesData);
        $customer->save();
        $this->_afterSave($customer, $customerData, $addressesData);

        return $this;
    }

    /**
     * Sets each value from data to entity \Magento\Object using setter method.
     *
     * @param \Magento\Object $entity
     * @param array $data
     */
    protected function _setDataUsingMethods($entity, array $data)
    {
        foreach ($data as $property => $value) {
            $entity->setDataUsingMethod($property, $value);
        }
    }

    /**
     * Trigger before save logic
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $customerData
     * @param array $addressesData
     */
    protected function _beforeSave($customer, array $customerData, array $addressesData = null)
    {
        if (!is_null($addressesData)) {
            $this->_prepareCustomerAddressesForSave($customer, $addressesData);
        }
        if (is_callable($this->_beforeSaveCallback)) {
            call_user_func_array($this->_beforeSaveCallback, array($customer, $customerData, $addressesData));
        }
    }

    /**
     * Trigger before save logic
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $customerData
     * @param array $addressesData
     */
    protected function _afterSave($customer, array $customerData, array $addressesData = null)
    {
        if (is_callable($this->_afterSaveCallback)) {
            call_user_func_array($this->_afterSaveCallback, array($customer, $customerData, $addressesData));
        }
        $this->_sendWelcomeEmail($customer, $customerData);
    }

    /**
     * Set customer password
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $customerData
     */
    protected function _preparePasswordForSave($customer, array $customerData)
    {
        $password = $this->_getCustomerPassword($customer, $customerData);
        if (!is_null($password)) {
            // 'force_confirmed' should be set in admin area only
            if ($this->_isAdminStore) {
                $customer->setForceConfirmed(true);
            }
            $customer->setPassword($password);
        }
    }

    /**
     * Get customer password
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $customerData
     * @return string|null
     */
    protected function _getCustomerPassword($customer, array $customerData)
    {
        $password = null;

        if ($this->_isAutogeneratePassword($customerData)) {
            $password = $customer->generatePassword();
        } elseif (isset($customerData['password'])) {
            $password = $customerData['password'];
        }

        return $password;
    }

    /**
     * Change customer password
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $customerData
     * @return \Magento\Customer\Service\Customer
     */
    protected function _changePassword($customer, array $customerData)
    {
        if (!empty($customerData['password']) || $this->_isAutogeneratePassword($customerData)) {
            $newPassword = $this->_getCustomerPassword($customer, $customerData);
            $customer->changePassword($newPassword);
            if ($this->_sendRemainderEmail) {
                $customer->sendPasswordReminderEmail();
            }
        }

        return $this;
    }

    /**
     * Check if password should be generated automatically
     *
     * @param array $customerData
     * @return bool
     */
    protected function _isAutogeneratePassword(array $customerData)
    {
        return isset($customerData['autogenerate_password']) && $customerData['autogenerate_password'];
    }

    /**
     * Retrieve send email flag
     *
     * @param array $customerData
     * @return bool
     */
    protected function _isSendEmail(array $customerData)
    {
        return isset($customerData['sendemail']) && $customerData['sendemail'];
    }

    /**
     * Send welcome email to customer
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $customerData
     * @return \Magento\Customer\Service\Customer
     */
    protected function _sendWelcomeEmail($customer, array $customerData)
    {
        if ($customer->getWebsiteId()
            && ($this->_isSendEmail($customerData) || $this->_isAutogeneratePassword($customerData))
        ) {
            $isNewCustomer = !(bool)$customer->getOrigData($customer->getIdFieldName());
            $storeId = $customer->getSendemailStoreId();

            if ($isNewCustomer) {
                $newLinkToken = $this->_dataHelper->generateResetPasswordLinkToken();
                $customer->changeResetPasswordLinkToken($newLinkToken);
                $customer->sendNewAccountEmail('registered', '', $storeId);
            } elseif (!$customer->getConfirmation()) {
                // Confirm not confirmed customer
                $customer->sendNewAccountEmail('confirmed', '', $storeId);
            }
        }
        return $this;
    }

    /**
     * Load customer by its ID
     *
     * @param int|string $customerId
     * @return \Magento\Customer\Model\Customer
     * @throws \Magento\Core\Exception
     */
    protected function _loadCustomerById($customerId)
    {
        $customer = $this->_customerFactory->create();
        $customer->load($customerId);
        if (!$customer->getId()) {
            throw new \Magento\Core\Exception(__("The customer with the specified ID not found."));
        }

        return $customer;
    }

    /**
     * Save customer addresses.
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $addressesData
     * @throws \Magento\Core\Exception
     */
    protected function _prepareCustomerAddressesForSave($customer, array $addressesData)
    {
        $hasChanges = $customer->hasDataChanges();
        $actualAddressesIds = array();
        foreach ($addressesData as $addressData) {
            $addressId = null;
            if (array_key_exists('entity_id', $addressData)) {
                $addressId = $addressData['entity_id'];
                unset($addressData['entity_id']);
            }

            if (null !== $addressId) {
                $address = $customer->getAddressItemById($addressId);
                if (!$address || !$address->getId()) {
                    throw new \Magento\Core\Exception(
                        __('The address with the specified ID not found.'));
                }
            } else {
                $address = $this->_addressFactory->create();
                $address->setCustomerId($customer->getId());
                // Add customer address into addresses collection
                $customer->addAddress($address);
            }
            $address->addData($addressData);
            $hasChanges = $hasChanges || $address->hasDataChanges();

            // Set post_index for detect default billing and shipping addresses
            $address->setPostIndex($addressId);

            $actualAddressesIds[] = $address->getId();
        }

        /** @var \Magento\Customer\Model\Address $address */
        foreach ($customer->getAddressesCollection() as $address) {
            if (!in_array($address->getId(), $actualAddressesIds)) {
                $address->setData('_deleted', true);
                $hasChanges = true;
            }
        }
        $customer->setDataChanges($hasChanges);
    }
}
