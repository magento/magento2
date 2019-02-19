<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Api\Data;

/**
 * Gateway vault payment token interface.
 *
 * @api
 * @since 100.1.0
 */
interface PaymentTokenInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Customer ID.
     */
    const CUSTOMER_ID = 'customer_id';
    /*
     * Unique hash for frontend.
     */
    const PUBLIC_HASH = 'public_hash';
    /*
     * Payment method code.
     */
    const PAYMENT_METHOD_CODE = 'payment_method_code';
    /*
     * Token type.
     */
    const TYPE = 'type';
    /*
     * Token creation timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Token expiration timestamp.
     */
    const EXPIRES_AT = 'expires_at';
    /*
     * Gateway token ID.
     */
    const GATEWAY_TOKEN = 'gateway_token';
    /*
     * Additional details.
     */
    const DETAILS = 'details';
    /*
     * Is vault payment record active.
     */
    const IS_ACTIVE = 'is_active';
    /*
     * Is vault payment token visible.
     */
    const IS_VISIBLE = 'is_visible';

    /**
     * Gets the entity ID.
     *
     * @return int|null Entity ID.
     * @since 100.1.0
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     * @since 100.1.0
     */
    public function setEntityId($entityId);

    /**
     * Gets the customer ID.
     *
     * @return int|null Customer ID.
     * @since 100.1.0
     */
    public function getCustomerId();

    /**
     * Sets customer ID.
     *
     * @param int $customerId
     * @return $this
     * @since 100.1.0
     */
    public function setCustomerId($customerId);

    /**
     * Get public hash
     *
     * @return string
     * @since 100.1.0
     */
    public function getPublicHash();

    /**
     * Set public hash
     *
     * @param string $hash
     * @return $this
     * @since 100.1.0
     */
    public function setPublicHash($hash);

    /**
     * Get payment method code
     *
     * @return string
     * @since 100.1.0
     */
    public function getPaymentMethodCode();

    /**
     * Set payment method code
     *
     * @param string $code
     * @return $this
     * @since 100.1.0
     */
    public function setPaymentMethodCode($code);

    /**
     * Get type
     *
     * @return string
     * @since 100.1.0
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     * @since 100.1.0
     */
    public function setType($type);

    /**
     * Get token creation timestamp
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getCreatedAt();

    /**
     * Set token creation timestamp
     *
     * @param string $timestamp
     * @return $this
     * @since 100.1.0
     */
    public function setCreatedAt($timestamp);

    /**
     * Get token expiration timestamp
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getExpiresAt();

    /**
     * Set token expiration timestamp
     *
     * @param string $timestamp
     * @return $this
     * @since 100.1.0
     */
    public function setExpiresAt($timestamp);

    /**
     * Get gateway token ID
     *
     * @return string
     * @since 100.1.0
     */
    public function getGatewayToken();

    /**
     * Set gateway token ID
     *
     * @param string $token
     * @return $this
     * @since 100.1.0
     */
    public function setGatewayToken($token);

    /**
     * Get token details
     *
     * @return string
     * @since 100.1.0
     */
    public function getTokenDetails();

    /**
     * Set token details
     *
     * @param string $details
     * @return $this
     * @since 100.1.0
     */
    public function setTokenDetails($details);

    /**
     * Gets is vault payment record active.
     *
     * @return bool Is active.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 100.1.0
     */
    public function getIsActive();

    /**
     * Sets is vault payment record active.
     *
     * @param bool $isActive
     * @return $this
     * @since 100.1.0
     */
    public function setIsActive($isActive);

    /**
     * Gets is vault payment record visible.
     *
     * @return bool Is visible.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 100.1.0
     */
    public function getIsVisible();

    /**
     * Sets is vault payment record visible.
     *
     * @param bool $isVisible
     * @return $this
     * @since 100.1.0
     */
    public function setIsVisible($isVisible);
}
