<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Vault\Api\Data\PaymentTokenExtensionInterface;
use Magento\Vault\Model\ResourceModel;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Vault Payment Token extension attribute model
 */
class PaymentToken extends AbstractModel implements PaymentTokenInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'vault_payment_token';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\PaymentToken::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->getData(PaymentTokenInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(PaymentTokenInterface::CUSTOMER_ID, $customerId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethodCode()
    {
        return $this->getData(PaymentTokenInterface::PAYMENT_METHOD_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethodCode($code)
    {
        $this->setData(PaymentTokenInterface::PAYMENT_METHOD_CODE, $code);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getData(PaymentTokenInterface::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->setData(PaymentTokenInterface::TYPE, $type);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(PaymentTokenInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($timestamp)
    {
        $this->setData(PaymentTokenInterface::CREATED_AT, $timestamp);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExpiresAt()
    {
        return $this->getData(PaymentTokenInterface::EXPIRES_AT);
    }

    /**
     * @inheritdoc
     */
    public function setExpiresAt($timestamp)
    {
        $this->setData(PaymentTokenInterface::EXPIRES_AT, $timestamp);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGatewayToken()
    {
        return $this->getData(PaymentTokenInterface::GATEWAY_TOKEN);
    }

    /**
     * @inheritdoc
     */
    public function setGatewayToken($token)
    {
        $this->setData(PaymentTokenInterface::GATEWAY_TOKEN, $token);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTokenDetails()
    {
        return $this->getData(PaymentTokenInterface::DETAILS);
    }

    /**
     * @inheritdoc
     */
    public function setTokenDetails($details)
    {
        $this->setData(PaymentTokenInterface::DETAILS, $details);
        return $this;
    }

    /**
     * Gets is vault payment record active.
     *
     * @return bool Is active.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive()
    {
        return (bool)$this->getData(PaymentTokenInterface::IS_ACTIVE);
    }

    /**
     * Sets is vault payment record active.
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->setData(PaymentTokenInterface::IS_ACTIVE, (int)$isActive);
        return $this;
    }

    /**
     * Get frontend hash
     *
     * @return string
     */
    public function getPublicHash()
    {
        return $this->getData(PaymentTokenInterface::PUBLIC_HASH);
    }

    /**
     * Set frontend hash
     *
     * @param string $hash
     * @return $this
     */
    public function setPublicHash($hash)
    {
        $this->setData(PaymentTokenInterface::PUBLIC_HASH, $hash);
        return $this;
    }

    /**
     * Gets is vault payment record visible.
     *
     * @return bool Is visible.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsVisible()
    {
        return (bool) (int) $this->getData(PaymentTokenInterface::IS_VISIBLE);
    }

    /**
     * Sets is vault payment record visible.
     *
     * @param bool $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible)
    {
        $this->setData(PaymentTokenInterface::IS_VISIBLE, (bool) $isVisible);
        return $this;
    }
}
