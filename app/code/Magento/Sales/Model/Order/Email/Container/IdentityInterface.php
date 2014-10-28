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
namespace  Magento\Sales\Model\Order\Email\Container;

use \Magento\Store\Model\Store;

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
