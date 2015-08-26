<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PasswordManagement\Model;

use Magento\Framework\Event\Observer as EventObserver;

/**
 * PasswordManagement backend observer model
 *
 * Implements hashes upgrading
 */
class Observer
{
    /**
     * PasswordManagement encryption model
     *
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(\Magento\Framework\Encryption\EncryptorInterface $encryptor)
    {
        $this->_encryptor = $encryptor;
    }

    /**
     * Upgrade customer password hash when customer has logged in
     *
     * @param EventObserver $observer
     * @return void
     */
    public function upgradeCustomerPassword($observer)
    {
        $password = $observer->getEvent()->getPassword();
        /** @var \Magento\Customer\Model\Customer $model */
        $model = $observer->getEvent()->getModel();
        if (!$this->_encryptor->validateHash($password, $model->getPasswordHash())) {
            $model->changePassword($password);
        }
    }
}
