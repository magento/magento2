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
namespace Magento\Sales\Helper;

use Magento\Store\Model\Store;

/**
 * Sales module base helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Core\Helper\Data
{
    /**
     * Maximum available number
     */
    const MAXIMUM_AVAILABLE_NUMBER = 99999999;

    /**
     * Check quote amount
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param float $amount
     * @return $this
     */
    public function checkQuoteAmount(\Magento\Sales\Model\Quote $quote, $amount)
    {
        if (!$quote->getHasError() && $amount >= self::MAXIMUM_AVAILABLE_NUMBER) {
            $quote->setHasError(true);
            $quote->addMessage(__('This item price or quantity is not valid for checkout.'));
        }
        return $this;
    }

    /**
     * Check allow to send new order confirmation email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendNewOrderConfirmationEmail($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\Sales\Model\Order\Email\Container\OrderIdentity::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check allow to send new order email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendNewOrderEmail($store = null)
    {
        return $this->canSendNewOrderConfirmationEmail($store);
    }

    /**
     * Check allow to send order comment email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendOrderCommentEmail($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check allow to send new shipment email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendNewShipmentEmail($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check allow to send shipment comment email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendShipmentCommentEmail($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check allow to send new invoice email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendNewInvoiceEmail($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check allow to send invoice comment email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendInvoiceCommentEmail($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check allow to send new creditmemo email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendNewCreditmemoEmail($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check allow to send creditmemo comment email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendCreditmemoCommentEmail($store = null)
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
