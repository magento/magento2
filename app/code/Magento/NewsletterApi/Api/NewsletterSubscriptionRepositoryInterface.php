<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api;

/**
 * Newsletter Subscription CRUD interface.
 *
 * @api
 */
interface NewsletterSubscriptionRepositoryInterface
{
    /**
     * Save newsletter subscription.
     *
     * @param \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionInterface $newsletterSubscription
     * @return \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save($newsletterSubscription);

    /**
     * Retrieve newsletter subscription.
     *
     * @param int $newsletterSubscriptionId
     * @return \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($newsletterSubscriptionId);

    /**
     * Retrieve newsletter subscriptions matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList($searchCriteria);

    /**
     * Delete newsletter subscription.
     *
     * @param \Magento\NewsletterApi\Api\Data\NewsletterSubscriptionInterface $newsletterSubscription
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete($newsletterSubscription);

    /**
     * Delete newsletter subscription by ID.
     *
     * @param int $newsletterSubscriptionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($newsletterSubscriptionId);
}
