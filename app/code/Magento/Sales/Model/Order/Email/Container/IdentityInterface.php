<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace  Magento\Sales\Model\Order\Email\Container;

use Magento\Store\Model\Store;

interface IdentityInterface
{
    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return array|bool
     */
    public function getEmailCopyTo();

    /**
     * @return mixed
     */
    public function getCopyMethod();

    /**
     * @return mixed
     */
    public function getGuestTemplateId();

    /**
     * @return mixed
     */
    public function getTemplateId();

    /**
     * @return mixed
     */
    public function getEmailIdentity();

    /**
     * @return string
     */
    public function getCustomerEmail();

    /**
     * @return string
     */
    public function getCustomerName();

    /**
     * @return Store
     */
    public function getStore();

    /**
     * @param Store $store
     * @return mixed
     */
    public function setStore(Store $store);

    /**
     * @param string $email
     * @return mixed
     */
    public function setCustomerEmail($email);

    /**
     * @param string $name
     * @return mixed
     */
    public function setCustomerName($name);
}
