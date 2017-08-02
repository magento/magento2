<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace  Magento\Sales\Model\Order\Email\Container;

use Magento\Store\Model\Store;

/**
 * Interface \Magento\Sales\Model\Order\Email\Container\IdentityInterface
 *
 * @since 2.0.0
 */
interface IdentityInterface
{
    /**
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled();

    /**
     * @return array|bool
     * @since 2.0.0
     */
    public function getEmailCopyTo();

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getCopyMethod();

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getGuestTemplateId();

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getTemplateId();

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getEmailIdentity();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCustomerEmail();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCustomerName();

    /**
     * @return Store
     * @since 2.0.0
     */
    public function getStore();

    /**
     * @param Store $store
     * @return mixed
     * @since 2.0.0
     */
    public function setStore(Store $store);

    /**
     * @param string $email
     * @return mixed
     * @since 2.0.0
     */
    public function setCustomerEmail($email);

    /**
     * @param string $name
     * @return mixed
     * @since 2.0.0
     */
    public function setCustomerName($name);
}
