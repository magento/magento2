<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\NewsletterApi\Api\Data\NewsletterSubscriptionExtensionInterface;

/**
 * Newsletter Subscription Interface
 *
 * @api
 */
interface NewsletterSubscriptionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SUBSCRIPTION_ID = 'subscription_id';
    const EMAIL = 'email';
    /**#@-*/

    /**
     * Set ID
     *
     * @param int $id
     * @return \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionInterface
     */
    public function setId($id);

    /**
     * Set E-Mail Address
     *
     * @param string $email
     * @return \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionInterface
     */
    public function setEmail($email);

    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Get E-Mail Address
     *
     * @return string
     */
    public function getEmail();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionExtensionInterface $extensionAttributes
     * @return \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionInterface
     */
    public function setExtensionAttributes($extensionAttributes);
}
