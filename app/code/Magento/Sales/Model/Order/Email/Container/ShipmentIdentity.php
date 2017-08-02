<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Email\Container;

/**
 * Class \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity
 *
 * @since 2.0.0
 */
class ShipmentIdentity extends Container implements IdentityInterface
{
    /**
     * Configuration paths
     */
    const XML_PATH_EMAIL_COPY_METHOD = 'sales_email/shipment/copy_method';
    const XML_PATH_EMAIL_COPY_TO = 'sales_email/shipment/copy_to';
    const XML_PATH_EMAIL_IDENTITY = 'sales_email/shipment/identity';
    const XML_PATH_EMAIL_GUEST_TEMPLATE = 'sales_email/shipment/guest_template';
    const XML_PATH_EMAIL_TEMPLATE = 'sales_email/shipment/template';
    const XML_PATH_EMAIL_ENABLED = 'sales_email/shipment/enabled';

    /**
     * Is email enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return list of copy_to emails
     *
     * @return array|bool
     * @since 2.0.0
     */
    public function getEmailCopyTo()
    {
        $data = $this->getConfigValue(self::XML_PATH_EMAIL_COPY_TO, $this->getStore()->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    /**
     * Return email copy method
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getCopyMethod()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStore()->getStoreId());
    }

    /**
     * Return guest template id
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getGuestTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return template id
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return email identity
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getEmailIdentity()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_IDENTITY, $this->getStore()->getStoreId());
    }
}
