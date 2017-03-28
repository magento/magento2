<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Helper;

use Magento\Store\Model\Store;

/**
 * Sales module base helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Check allow to send new order confirmation email
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canSendNewOrderConfirmationEmail($store = null)
    {
        return $this->scopeConfig->isSetFlag(
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
        return $this->scopeConfig->isSetFlag(
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
        return $this->scopeConfig->isSetFlag(
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
        return $this->scopeConfig->isSetFlag(
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
        return $this->scopeConfig->isSetFlag(
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
        return $this->scopeConfig->isSetFlag(
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
        return $this->scopeConfig->isSetFlag(
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
        return $this->scopeConfig->isSetFlag(
            \Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
