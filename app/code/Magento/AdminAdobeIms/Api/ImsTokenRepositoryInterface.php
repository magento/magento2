<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Api;

use Magento\AdminAdobeIms\Api\Data\ImsTokenInterface;

use Magento\AdminAdobeIms\Api\Data\ImsTokenSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Declare ims user profile repository
 * @api
 */
interface ImsTokenRepositoryInterface
{
    /**
     * Save ims
     *
     * @param ImsTokenInterface $entity
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(ImsTokenInterface $entity): void;

    /**
     * Get ims token
     *
     * @param int $entityId
     * @return ImsTokenInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ImsTokenInterface;

    /**
     * Get ims token by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ImsTokenSearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ImsTokenSearchResultsInterface;

    /**
     * Delete ims token.
     *
     * @param int $id
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteByUserId(int $id): bool;
}
