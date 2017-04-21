<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\NewsletterApi\Api\Data\SubscriptionExtensionInterface;

/**
 * Newsletter Subscription Interface
 *
 * represents one newsletter subscription identified by an
 * e-mail address or the internal id
 *
 * @api
 */
interface SubscriptionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SUBSCRIPTION_ID = 'subscription_id';
    const EMAIL = 'email';
    const STATUS = 'status';
    /**#@-*/

    /**
     * Set ID
     *
     * set the internal id of the entity
     *
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * Set E-Mail Address
     *
     * set the e-mail address of the subscriber
     *
     * @param string $email
     * @return void
     */
    public function setEmail(string $email);

    /**
     * Set Status
     *
     * set the status of the subscription
     *
     * @param int $status
     * @return void
     */
    public function setStatus(int $status);

    /**
     * Get ID
     *
     * get the internal id of the subscription entity
     *
     * @return int
     */
    public function getId();

    /**
     * Get E-Mail Address
     *
     * get the email address of the subscriber
     *
     * @return string
     */
    public function getEmail();

    /**
     * Get Status
     *
     * get the status of the subscription
     *
     * @return int
     */
    public function getStatus();

    /**
     * Get Extension Attributes
     *
     * Retrieve existing extension attributes object.
     * Create a new one if not already initialized
     *
     * @return \Magento\NewsletterApi\Api\Data\SubscriptionExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NewsletterApi\Api\Data\SubscriptionExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(SubscriptionExtensionInterface $extensionAttributes);
}
