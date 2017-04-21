<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\NewsletterApi\Api\Data\SubscriptionInterface;

/**
 * Newsletter Subscription CRUD interface.
 *
 * interface to handle newsletter subscription entities internally
 *
 * @api
 */
interface SubscriptionRepositoryInterface
{
    /**
     * Save newsletter subscription.
     *
     * Persists a newsletter subscription entity and returns
     * the internal id of the created object
     *
     * @param \Magento\NewsletterApi\Api\Data\SubscriptionInterface $subscription subscription entity to be saved
     *
     * @return int the id of the newly created object
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException when an error occurred while persisting the entity
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     * @throws \Magento\Framework\Exception\AlreadyExistsException if the subscription already exists
     */
    public function save(SubscriptionInterface $subscription);

    /**
     * Retrieve newsletter subscription.
     *
     * retrieve a subscription entity from the persistence layer
     * by providing the internal id of the entity
     *
     * @param int $subscriptionId the internal id of the entity
     *
     * @return \Magento\NewsletterApi\Api\Data\SubscriptionInterface the entity with the given id
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException if no entity with this id exists
     * @throws \Magento\Framework\Exception\InputException if an invalid id was provided
     */
    public function getById(int $subscriptionId);

    /**
     * Retrieve newsletter subscription list
     *
     * get a list object containing subscription entities by specifying
     * search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria containing the search criteria for the list
     *
     * @return \Magento\NewsletterApi\Api\Data\SubscriptionSearchResultsInterface list containing the search result
     *
     * @throws \Magento\Framework\Exception\InputException if invalid search criteria have been provided
     * @throws \Magento\Framework\Exception\NotFoundException if no object matched the given criteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete newsletter subscription.
     *
     * delete a subscription entity from the underlying persistence
     * layer by providing the entity to delete
     *
     * @param \Magento\NewsletterApi\Api\Data\SubscriptionInterface $subscription the entity to be deleted
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\StateException when the entity is in an invalid state for deletion
     * @throws \Magento\Framework\Exception\NoSuchEntityException when the entity does not exist
     * @throws \Magento\Framework\Exception\CouldNotDeleteException when an error occurred during deletion
     */
    public function delete(SubscriptionInterface $subscription);

    /**
     * Delete newsletter subscription by ID.
     *
     * delete a subscription entity from the underlying persistence
     * layer by providing the internal id of the entity to delete
     *
     * @param int $subscriptionId id of the entity to be deleted
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\StateException when the entity is in an invalid state for deletion
     * @throws \Magento\Framework\Exception\NoSuchEntityException when an entity with given id does not exist
     * @throws \Magento\Framework\Exception\CouldNotDeleteException when an error occurred during deletion
     */
    public function deleteById(int $subscriptionId);
}
