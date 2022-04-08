<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Api;

use Magento\AdminAdobeIms\Api\Data\ImsWebapiInterface;

use Magento\AdminAdobeIms\Api\Data\ImsWebapiSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Declare ims user profile repository
 * @api
 */
interface ImsWebapiRepositoryInterface
{
    /**
     * Save ims
     *
     * @param ImsWebapiInterface $entity
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(ImsWebapiInterface $entity): void;

    /**
     * Get ims token
     *
     * @param int $entityId
     * @return ImsWebapiInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ImsWebapiInterface;

    /**
     * Get ims token(s) by admin id
     *
     * @param int $adminId
     * @return ImsTokenInterface[]
     * @throws NoSuchEntityException
     */
    public function getByAdminId(int $adminId): array;

    /**
     * Get ims token by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ImsWebapiSearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ImsWebapiSearchResultsInterface;

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
