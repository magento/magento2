<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api\Data;

/**
 * Newsletter Subscription Management Interface
 *
 * @api
 */
interface NewsletterSubscriptionManagementInterface
{
    /**
     * @param \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionInterface $newsletterSubscription
     * @return \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionInterface
     * @throws \Magento\NewsletterApi\Exception\AlreadySubscribedException
     */
    public function subscribe($newsletterSubscription);

    /**
     * @param string $email
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function unsubscribe($email);
}
