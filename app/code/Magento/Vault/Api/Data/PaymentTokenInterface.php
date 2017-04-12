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
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Gets the customer ID.
     *
     * @return int|null Customer ID.
     */
    public function getCustomerId();

    /**
     * Sets customer ID.
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get public hash
     *
     * @return string
     */
    public function getPublicHash();

    /**
     * Set public hash
     *
     * @param string $hash
     * @return $this
     */
    public function setPublicHash($hash);

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getPaymentMethodCode();

    /**
     * Set payment method code
     *
     * @param string $code
     * @return $this
     */
    public function setPaymentMethodCode($code);

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get token creation timestamp
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set token creation timestamp
     *
     * @param string $timestamp
     * @return $this
     */
    public function setCreatedAt($timestamp);

    /**
     * Get token expiration timestamp
     *
     * @return string|null
     */
    public function getExpiresAt();

    /**
     * Set token expiration timestamp
     *
     * @param string $timestamp
     * @return $this
     */
    public function setExpiresAt($timestamp);

    /**
     * Get gateway token ID
     *
     * @return string
     */
    public function getGatewayToken();

    /**
     * Set gateway token ID
     *
     * @param string $token
     * @return $this
     */
    public function setGatewayToken($token);

    /**
     * Get token details
     *
     * @return string
     */
    public function getTokenDetails();

    /**
     * Set token details
     *
     * @param string $details
     * @return $this
     */
    public function setTokenDetails($details);

    /**
     * Gets is vault payment record active.
     *
     * @return bool Is active.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive();

    /**
     * Sets is vault payment record active.
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Gets is vault payment record visible.
     *
     * @return bool Is visible.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsVisible();

    /**
     * Sets is vault payment record visible.
     *
     * @param bool $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible);
}
